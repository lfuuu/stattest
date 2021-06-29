<?php

use app\classes\ActOfReconciliation;
use app\classes\api\PayPal;
use app\classes\Assert;
use app\classes\Encrypt;
use app\classes\Language;
use app\dao\NumberBeautyDao;
use app\dao\reports\ReportUsageDao;
use app\exceptions\ModelValidationException;
use app\forms\usage\UsageVoipEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\City;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\Country;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\Invoice;
use app\models\Language as LanguageModel;
use app\models\LkClientSettings;
use app\models\LkNoticeSetting;
use app\models\LogTarif;
use app\models\Number;
use app\models\Payment as PaymentModel;
use app\models\Region;
use app\models\TariffVirtpbx;
use app\models\TariffVoip;
use app\models\Trouble;
use app\models\usages\UsageInterface;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\User;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\Bill as uuBill;
use yii\db\Expression;

class ApiLk
{

    const MAX_LK_NOTIFICATION_CONTACTS = 6;

    const DEFAULT_MANAGER = User::DEFAULT_ACCOUNT_MANAGER_USER;

    /**
     * @param $clientId
     * @return ClientAccount
     */
    private static function getAccount($clientId)
    {
        return ClientAccount::findOne(['id' => $clientId]);
    }

    public static function getBalanceList($clientId)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId)) {
            throw new Exception("account_is_bad");
        }

        $account = self::getAccount($clientId);
        if (!$account) {
            throw new Exception("account_not_found");
        }

        $params = ["client_id" => $account->id, "client_currency" => $account->currency, 'is_from_lk' => true];

        if ($account->getUuCountryId() != Country::RUSSIA) {
            $params['to_date'] = '2019-08-01';
        }

        list($R, $sum,) = BalanceSimple::get($params);

        $cutOffDate = '2000-01-01';

        $bills = [];

        if (!isset($params['to_date']) && $account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $bills[] = [
                'bill_no' => 'current_statement',
                'bill_date' => (new DateTime('now', new DateTimeZone($account->timezone_name)))->format(DateTimeZoneHelper::DATE_FORMAT),
                'sum' => number_format(uuBill::getUnconvertedAccountEntries($account->id)->sum('price_with_vat'), 2, '.', ''),
                'type' => '',
                'pays' => [],
                'link' => []
            ];
        }

        $clientCountryId = $account->country_id;

        $organizationId = $account->contract->organization_id;

        foreach ($R as $r) {
            if (strtotime($r["bill"]["bill_date"]) < $cutOffDate) {
                continue;
            }

            $b = $r["bill"];

            $billModel = Bill::findOne(['bill_no' => $b["bill_no"]]);

            $bill = [
                'bill_no' => $b['bill_no'],
                'bill_date' => $b['bill_date'],
                'sum' => $b['sum'],
                'type' => $b['nal'],
                'pays' => [],
                'link' => self::_getBillDocumentLinks($billModel, $clientCountryId, $organizationId)
            ];

            foreach ($r["pays"] as $p) {
                if (strtotime($p["payment_date"]) < $cutOffDate) {
                    continue;
                }

                $bill["pays"][] = [
                    "no" => $p["payment_no"],
                    "date" => $p["payment_date"],
                    "type" => self::_getPaymentTypeName($p),
                    "sum" => $p["sum"]
                ];
            }

            if ($b["is_show_in_lk"]) {
                $bills[] = $bill;
            }
        }

        $sum = $sum["RUB"];

        $p = Payment::first([
                "select" => "sum(`sum`) as sum",
                "conditions" => ["client_id" => $account->id]
            ]
        );

        $nSum = [
            "payments" => $p ? $p->sum : 0.00,
            "bills" => $sum["bill"],
            "saldo" => $sum["delta"],
            "saldo_date" => $sum["ts"]
        ];

        return ["bills" => $bills, "sums" => $nSum];
    }

    public static function getInvoiceBalance($clientId)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId)) {
            throw new Exception("account_is_bad");
        }

        $account = self::getAccount($clientId);
        if (!$account) {
            throw new Exception("account_not_found");
        }

        return ActOfReconciliation::me()->getData(
            $account,
            null,
            (new DateTimeImmutable('now'))
                ->modify('last day of this month')
                ->format(DateTimeZoneHelper::DATE_FORMAT)
        );
    }

    public static function getUserBillOnSum($clientId, $sum)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId)) {
            throw new Exception("account_is_bad");
        }

        $sum = (float)$sum;

        if (!$sum || $sum < 1 || $sum > 1000000) {
            throw new Exception("data_error");
        }


        $account = self::getAccount($clientId);
        if (!$account) {
            throw new Exception("account_not_found");
        }

        $billNo = Bill::dao()->getPrepayedBillNoOnSumFromDB($clientId, $sum, $account->currency);

        if (!$billNo) {

            NewBill::createBillOnPay($clientId, $sum, $account->currency, true);

            $billNo = Bill::dao()->getPrepayedBillNoOnSumFromDB($clientId, $sum, $account->currency);
        }

        if (!$billNo) {
            throw new Exception("account_error_create");
        }

        return $billNo;
    }

    public static function getBillUrl($billNo)
    {
        $bill = Bill::findOne(["bill_no" => $billNo]);
        if (!$bill) {
            throw new Exception("bill_not_found");
        }

        if (!defined('API__print_bill_url') || !API__print_bill_url) {
            throw new Exception("Не установлена ссылка на печать документов");
        }

        $R = [
                'bill' => $billNo,
                'object' => 'bill-2-RUB',
                'client' => $bill->client_id
            ] + (
            $bill->clientAccount->country_id != Country::RUSSIA ?
                ['doc_type' => \app\classes\documents\DocumentReport::DOC_TYPE_PROFORMA] :
                []
            );
        return API__print_bill_url . Encrypt::encodeArray($R);
    }

    public static function getReceiptURL($clientId, $sum)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId)) {
            throw new Exception("account_is_bad");
        }

        $sum = (float)$sum;

        if (!$sum || $sum < 1 || $sum > 1000000) {
            throw new Exception("data_error");
        }


        $account = self::getAccount($clientId);
        if (!$account) {
            throw new Exception("account_not_found");
        }

        $R = ["sum" => $sum, 'object' => "receipt-2-RUB", 'client' => $account->id];
        return API__print_bill_url . Encrypt::encodeArray($R);
    }

    public static function getPropertyPaymentOnCard($clientId, $sum)
    {
        global $db;

        if (!defined("UNITELLER_SHOP_ID") || !defined("UNITELLER_PASSWORD")) {
            throw new Exception("Не заданы параметры для UNITELLER в конфиге");
        }

        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId)) {
            throw new Exception("account_is_bad");
        }

        $sum = (float)$sum;

        if (!$sum || $sum < 1 || $sum > 1000000) {
            throw new Exception("data_error");
        }


        $account = self::getAccount($clientId);
        if (!$account) {
            throw new Exception("account_not_found");
        }


        $sum = number_format($sum, 2, '.', '');
        $orderId = $db->QueryInsert('payments_orders', [
                'type' => 'card',
                'client_id' => $clientId,
                'sum' => $sum
            ]
        );

        $signature = strtoupper(md5(UNITELLER_SHOP_ID . $orderId . $sum . UNITELLER_PASSWORD));

        return ["sum" => $sum, "order" => $orderId, "signature" => $signature];
    }

    public static function updateUnitellerOrder($orderId)
    {
        return true;
        exit();
    }

    public static function getBill($clientId, $billNo)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId)) {
            throw new Exception("account_is_bad");
        }

        $account = self::getAccount($clientId);
        if (!$account) {
            throw new Exception("account_not_found");
        }

        $accountCountryId = $account->country_id;

        if ($billNo == uuBill::CURRENT_STATEMENT) {

            $lines = [];
            $sum = 0;

            $query = uuBill::getUnconvertedAccountEntries($account->id);
            foreach ($query->each() as $uuLine) {
                $lines[] = [
                    'item' => $uuLine->getFullName(),
                    'date_from' => '',
                    'amount' => 1,
                    'price' => number_format($uuLine->price_with_vat, 2, '.', ''),
                    'sum' => number_format($uuLine->price_with_vat, 2, '.', '')
                ];

                $sum += $uuLine['price_with_vat'];
            }

            return [
                "bill" => [
                    "bill_no" => 'current_statement',
                    "is_rollback" => 0,
                    "is_1c" => 0,
                    "lines" => $lines,
                    "sum_total" => $sum,
                    "dtypes" => ['bill_no' => 'current_statement', 'ts' => time()]
                ],
                "link" => [
                ],
            ];
        }

        $billModel = Bill::findOne(["client_id" => $clientId, "bill_no" => $billNo, "is_show_in_lk" => 1]);

        if (!$billModel) {
            throw new Exception("bill_not_found");
        }

        $organizationId = $account->contract->organization_id;

        $lines = [];

        /** @var \app\models\BillLine $line */
        foreach ($billModel->lines as $line) {
            $lines[] = [
                "item" => $line->item,
                "date_from" => $line->date_from ? (new DateTime($line->date_from))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE) : "",
                "amount" => $line->amount,
                "price" => number_format($line->price, 2, '.', ''),
                "sum" => number_format($line->sum, 2, '.', '')
            ];
        }

        $ret = [
            "bill" => [
                "bill_no" => $billModel->bill_no,
                "is_rollback" => $billModel->is_rollback,
                "is_1c" => $billModel->is1C(),
                "lines" => $lines,
                "sum_total" => number_format($billModel->sum, 2, '.', ''),
                "dtypes" => ["bill_no" => $billModel->bill_no, "ts" => (new DateTime($billModel->bill_date))->getTimestamp()]
            ],
            "link" => self::_getBillDocumentLinks($billModel, $accountCountryId, $organizationId),
        ];

        return $ret;
    }

    /**
     * Ссылки на документы в ЛК
     *
     * @param Bill $bill
     * @param integer $clientCountryId
     * @param integer $organizationId
     * @return array
     */
    private static function _getBillDocumentLinks(Bill $bill, $clientCountryId, $organizationId)
    {
        $dt = $bill->document;

        if (
            !$dt
            || \app\models\Organization::isMcnTeleсomKft($organizationId)
            || $bill->isCorrectionType()
        ) {
            return [];
        }

        $data = [];

        if ($clientCountryId == Country::RUSSIA && $bill->isHavePartnerRewards()) {
            $data['partner_reward'] = API__print_bill_url . Encrypt::encodeArray([
                    'object' => 'partner_reward',
                    'bill' => $bill->bill_no,
                    'client' => $bill->client_id,
                    'is_pdf' => 1,
                ]);

            return $data;
        }


        if ($clientCountryId == Country::RUSSIA || $bill->is_user_prepay) {

            $billData = [
                'bill' => $bill->bill_no,
                'object' => 'bill-2-RUB',
                'client' => $bill->client_id
            ];

            // у контрагентов вне России другая форма "счета"
            if ($clientCountryId != Country::RUSSIA) {
                $billData['doc_type'] = 'proforma';
                $billData['is_pdf'] = 1;
            }

            $data['bill'] = API__print_bill_url . Encrypt::encodeArray($billData);

        }

        // Универсальные инвойсы только для пользователей вне России
        if ($bill->uu_bill_id && $clientCountryId != Country::RUSSIA) {
            $data['invoice'] = API__print_bill_url . Encrypt::encodeArray([
                    'doc_type' => 'uu_invoice',
                    'bill' => $bill->bill_no,
                    'client' => $bill->client_id,
                    'is_pdf' => 1,
                ]);
        }

        if ($clientCountryId != Country::RUSSIA) {
            return $data;
        }

        $invoices = [];
        $isUseInvoice = false;

        if ($bill->bill_date >= Invoice::DATE_ACCOUNTING) {
            $invoices = $bill->invoices;

            $isUseInvoice = true;
        }

        $conf = [
            'i1' => [['object' => 'invoice-1', 'key' => 'invoice1', 'obj' => 1]],
            'i2' => [['object' => 'invoice-2', 'key' => 'invoice2', 'obj' => 2]],
            'a1' => [['object' => 'akt-1', 'key' => 'akt1', 'obj' => 1]],
            'a2' => [['object' => 'akt-2', 'key' => 'akt2', 'obj' => 2]],
            'ia1' => [['object' => 'upd-1', 'key' => 'upd1', 'obj' => 1]],
            'ia2' => [['object' => 'upd-2', 'key' => 'upd2', 'obj' => 2]],
            'i3' => [['object' => 'upd-3', 'key' => 'updt', 'obj' => 3], ['object' => 'lading', 'key' => 'lading', 'obj' => null]],
        ];


        foreach ($conf as $dtKey => $dtConf) {
            if (isset($dt[$dtKey]) && $dt[$dtKey]) {

                if ($isUseInvoice && $dtConf[0]['obj'] && !isset($invoices[$dtConf[0]['obj']])) { // пропускаем документы, которых нет в "книге" продаж
                    continue;
                }

                $data[$dtConf[$dt[$dtKey] - 1]['key']] = API__print_bill_url . Encrypt::encodeArray([
                        'bill' => $bill->bill_no,
                        'object' => $dtConf[$dt[$dtKey] - 1]['object'],
                        'client' => $bill->client_id
                    ]);
            }
        }

        return $data;
    }

    public static function getDomainList($clientId)
    {
        $ret = [];

        foreach (NewBill::find_by_sql('
				SELECT
					`d`.`id`,
					CAST(`d`.`actual_from` AS DATE) AS `actual_from`,
					CAST(`d`.`actual_to` AS DATE) AS `actual_to`,
					`d`.`domain`,
					`d`.`paid_till`,
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 1, 0) AS `actual`
				FROM
					`domains` AS `d`
				INNER JOIN `clients` ON (`d`.`client` = `clients`.`client`)
				WHERE
					`clients`.`id`=?
				ORDER BY
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 0, 1) ASC,
					`actual_from` DESC
				', [$clientId]) as $d) {
            $ret[] = self::_exportModelRow(["id", "actual_from", "actual_to", "domain", "paid_till", "actual"], $d);
        }

        return $ret;
    }

    public static function getEmailList($clientId)
    {

        $ret = [];

        foreach (NewBill::find_by_sql('
				SELECT
					`e`.`id`,
					CAST(`e`.`actual_from` AS DATE) AS `actual_from`,
					CAST(`e`.`actual_to` AS DATE) AS `actual_to`,
					`e`.`local_part`,
					`e`.`domain`,
					`e`.`box_size`,
					`e`.`box_quota`,
					`e`.`status`,
                    `e`.`spam_act`,
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 1, 0) AS `actual`
				FROM `emails` AS `e`
				INNER JOIN `clients` ON (`e`.`client` = `clients`.`client`)
				WHERE
					`clients`.`id` = ?
				ORDER BY
					`local_part`,
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 0, 1) ASC,
					`actual_from` DESC
				', [$clientId]) as $e) {

            $line = self::_exportModelRow(["id", "actual_from", "actual_to", "local_part", "domain", "box_size", "box_quota", "status", "actual", "spam_act"], $e);
            $line['email'] = $line['local_part'] . '@' . $line['domain'];
            $ret[] = $line;

        }

        return $ret;
    }

    /**
     * @param int $clientAccountId
     * @return array
     * @throws Exception
     */
    public static function getVoipTariffTree($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);

        if (!$clientAccount) {
            throw new Exception("account_not_found");
        }

        $priceLevel =  max(ClientAccount::DEFAULT_PRICE_LEVEL, $clientAccount->price_level);

        $cities = array_merge(
            [['id' => Number::TYPE_7800, 'name' => '800']],
            City::find()
                ->select(['id', 'name'])
                ->where([
                    'in_use' => 1,
                    'is_show_in_lk' => City::IS_SHOW_IN_LK_FULL,
                    'country_id' => $clientAccount->country_id
                ])
                ->orderBy(['order' => SORT_ASC])
                ->asArray()
                ->all()
        );

        $didGroupsByCity = DidGroup::dao()->getDidGroupsByCity($clientAccount->country_id);

        $didGroupsByCityId = [];

        /** @var DidGroup $didGroup */
        foreach ($didGroupsByCity as $cityId => $cityData) {
            foreach ($cityData as $didGroup) {
                $didGroupsByCityId[$cityId][] = [
                    'id' => $didGroup->id,
                    'code' => 'group_' . $didGroup->beauty_level,
                    'comment' => $didGroup->comment,
                    'activation_fee' => $didGroup->getPrice($priceLevel),
                    'currency_id' => $didGroup->country->currency_id,
                    'promo_info' => $didGroup->country_code == Country::RUSSIA && $didGroup->beauty_level == DidGroup::BEAUTY_LEVEL_STANDART
                ];
            }
        }

        return [
            'cities' => $cities,
            'didGroupsByCityId' => $didGroupsByCityId
        ];
    }

    public static function getVoipList($clientId, $isSimple = false)
    {
        $ret = [];

        $account = self::getAccount($clientId);

        if (!$account) {
            return $ret;
        }

        // По каждому номеру (E164) выбрать активную запись.
        // Если активной нет, то последнюю активную в недалеком прошлом (2 месяца) или в любом будущем.
        // Средствами SQL эту логику делать слишком извращенно. Проще выбрать все и отфильтровать лишнее средствами PHP
        $usageRows =
            Yii::$app->db->createCommand("
                    SELECT
                        id,
                        E164 AS `number`,
                        actual_from,
                        actual_to,
                        no_of_lines,
                        CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to AS actual,
                        CAST(NOW() AS DATE) <= actual_to AND actual_to < '" . UsageInterface::MIDDLE_DATE . "' AS is_will_be_off,
                        actual_to BETWEEN CAST(NOW() - interval 2 month AS DATE) AND CAST(NOW() AS DATE) AS actual_present_perfect,
                        CAST(NOW() AS DATE) < actual_from AS actual_future_indefinite,
                        region
                    FROM
                        usage_voip
                    WHERE
                        client = :client
                   ORDER BY
                        actual_from DESC
                ",
                [':client' => $account->client]
            )->queryAll();

        foreach ($usageRows as $usageRow) {
            $line = $usageRow;
            unset($line['actual_present_perfect'], $line['actual_future_indefinite']); // это временное служебное поле, которое не надо отдавать

            $usage = app\models\UsageVoip::findOne(["id" => $usageRow['id']]);

            $line["tarif_name"] = $usage->tariff->name;
            $line["per_month"] = number_format($usage->getAbonPerMonth(), 2, ".", " ");

            //$line["vpbx"] = virtPbx::number_isOnVpbx($clientId, $line["number"]) ? 1 : 0;
            $line["vpbx"] = 0;

            // даты начала и конца расширить до максимальных, не зависимо от того, активно оно сейчас или нет
            if (!$isSimple && isset($ret[$usageRow['number']])) {
                $ret[$usageRow['number']]['actual_from'] = $line['actual_from'] = min($ret[$usageRow['number']]['actual_from'], $line['actual_from']);
                $ret[$usageRow['number']]['actual_to'] = $line['actual_to'] = max($ret[$usageRow['number']]['actual_to'], $line['actual_to']);
            }

            // активные сейчас - всегда записываем
            // активные в недавнем прошлом или в будущем - только если нет активных сейчас
            if ($usageRow['actual'] ||
                (($usageRow['actual_present_perfect'] || $usageRow['actual_future_indefinite']) && !isset($ret[$usageRow['number']]))
            ) {
                $ret[$usageRow['number']] = $isSimple ? $line["number"] : $line;
            }
        }

        return array_values($ret);
    }

    public static function getVpbxList($clientId)
    {
        $ret = [];

        foreach (NewBill::find_by_sql('
            SELECT 
                a.*, 
                `t`.description AS tarif_name,
                `t`.`price`,
                `t`.`space`,
                `t`.`num_ports`
                 FROM (
                    SELECT
                        `u`.`id`,
                        `u`.`amount`,
                        `u`.`actual_to`,
                        CAST(NOW() AS DATE) BETWEEN `u`.`actual_from` AND `u`.`actual_to` AS `actual`,
                        CAST(NOW() AS DATE) <= u.actual_to AND u.actual_to < "' . UsageInterface::MIDDLE_DATE . '" AS is_will_be_off,
                        `u`.`status`,
                        `r`.`id` AS region_id,
                        (SELECT id_tarif FROM log_tarif WHERE service="usage_virtpbx" AND id_service=u.id AND date_activation<NOW() ORDER BY date_activation DESC, id DESC LIMIT 1) AS cur_tarif_id,
                        (SELECT date_activation FROM log_tarif WHERE service="usage_virtpbx" AND id_service=u.id AND date_activation<now() ORDER BY date_activation DESC, id DESC LIMIT 1) AS actual_from
                    FROM
                        `usage_virtpbx` AS `u`
                    INNER JOIN `clients` ON (
                        `u`.`client` = `clients`.`client`
                    )
                    LEFT JOIN `regions` AS `r` ON (
                        `u`.`region` = `r`.`id`
                    )
                    WHERE
                        `clients`.`id`= ?
                    ORDER BY
                        `actual` DESC,
                        `actual_from` DESC
                )a
                LEFT JOIN `tarifs_virtpbx` AS `t` ON (`t`.`id` = cur_tarif_id)
            ', [$clientId]) as $v) {
            $line = self::_exportModelRow(["id", "amount", "status", "actual_from", "actual_to", "actual", "is_will_be_off", "tarif_name", "price", "space", "num_ports", "region_id"], $v);
            $line["price"] = number_format($line["price"], 2, ".", " ");
            $line["amount"] = (float)$line["amount"];
            $ret[] = $line;
        }

        return $ret;
    }

    public static function getInternetList($clientId)
    {
        return self::_getConnectionsByType($clientId);
    }

    public static function getCollocationList($clientId)
    {
        return self::_getConnectionsByType($clientId, "C");
    }

    private static function _getConnectionsByType($clientId, $connectType = "I")
    {
        $ret = [];

        foreach (NewBill::find_by_sql('
				SELECT
					a.*,
					ti.name as tarif,
					ti.adsl_speed
				FROM (
					SELECT
						u.*,
						IF((u.actual_from<=NOW()) and (u.actual_to>NOW()),1,0) as actual,
						p.port_name as port,
						p.node,
						p.port_type,
						IF(u.actual_from<=(NOW()+INTERVAL 5 DAY),1,0) as actual5d,
						(SELECT
							t.id
						 FROM
							`log_tarif` AS `l`, `tarifs_internet` AS `t`
						 WHERE
							    `l`.`service` = "usage_ip_ports"
							AND `l`.`id_service` = u.id
							AND `t`.`id` = `l`.`id_tarif`
						 ORDER BY
							date_activation DESC,
							l.id DESC
						 LIMIT 1
						 ) AS tarif_id
					FROM usage_ip_ports u
					INNER JOIN clients c ON (c.client= u.client)
					LEFT JOIN tech_ports p ON (p.id=u.port_id)
					LEFT JOIN usage_ip_routes r ON (u.id=r.port_id)
					WHERE c.id = ?
					GROUP BY
						u.id
					order by
						actual desc,
						actual_from desc
					) a
				INNER JOIN tarifs_internet ti ON (ti.id = a.tarif_id)
				WHERE
					ti.type = ?
					', [$clientId, $connectType]) as $i) {
            $line = self::_exportModelRow(["id", "address", "actual_from", "actual_to", "actual", "tarif", "port", "port_type", "status", "adsl_speed", "node"], $i);

            $line["nets"] = self::_getInternet_nets($line["id"]);
            $line["cpe"] = self::_getInternet_cpe($line["id"]);

            $ret[] = $line;
        }

        return $ret;
    }

    private static function _getInternet_nets($portId)
    {
        $ret = [];

        foreach (NewBill::find_by_sql('
				SELECT
					*,
					IF (`actual_from` <= NOW() and `actual_to` > NOW(), 1, 0) as `actual`
				FROM
					`usage_ip_routes`
				WHERE
					(port_id= ? )
				AND `actual_from` <= NOW()
				AND `actual_to` > NOW()
				ORDER BY
					`actual` DESC,
					`actual_from` DESC
				', [$portId]) as $net) {
            $ret[] = self::_exportModelRow(explode(",", "id,actual_from,actual_to,net,type,actual"), $net);
        }
        return $ret;
    }

    public static function _getInternet_cpe($portId)
    {
        $ret = [];

        foreach (NewBill::find_by_sql("
					SELECT
						`usage_tech_cpe`.*,
						`type`,
						`vendor`,
						`model`,
						IF (`actual_from` <= NOW() AND `actual_to` >= NOW(), 1, 0) as `actual`
					FROM
						`usage_tech_cpe`
					INNER JOIN `tech_cpe_models` ON `tech_cpe_models`.`id` = `usage_tech_cpe`.`id_model`
					WHERE
							`usage_tech_cpe`.`service` = 'usage_ip_ports'
						AND `usage_tech_cpe`.`id_service` = ?
						AND (`actual_from` <= NOW() AND `actual_to` >= NOW())
					ORDER BY
						`actual` DESC,
						`actual_from` DESC
					", [$portId]) as $cpe) {
            $ret[] = self::_exportModelRow(["actual_from", "actual_to", "ip", "type", "vendor", "model", "actual", "numbers"], $cpe);
        }

        return $ret;
    }

    public static function getExtraList($clientId)
    {
        $ret = [];

        foreach (NewBill::find_by_sql("
					SELECT
						`u`.`id`,
						`u`.`actual_from`,
						`u`.`actual_to`,
						`u`.`amount`,
						`t`.`description`,
						`t`.`period`,
						`t`.`price`,
						`t`.`param_name`,
						`u`.`param_value`,
						IF ((`actual_from` <= CAST(NOW() AS DATE)) AND (`actual_to` > CAST(NOW() AS DATE)), 1, 0) AS `actual`,
						IF ((`actual_from` <= (CAST(NOW() AS DATE) + INTERVAL 5 DAY)), 1, 0) AS `actual5d`
					FROM
						`usage_extra` AS `u`
					INNER JOIN `clients` ON (`clients`.`client` = `u`.`client`)
					LEFT JOIN `tarifs_extra` AS `t` ON t.id=u.tarif_id
					WHERE
						`clients`.`id` = ?
					AND `actual_from` <= CAST(NOW() AS DATE)
					AND `actual_to` > CAST(NOW() AS DATE)
					ORDER BY
						`actual` DESC,
						`actual_from` DESC
				", [$clientId]) as $service) {
            $line = self::_exportModelRow(["id", "actual_from", "actual_to", "amount", "description", "period", "price", "param_name", "param_value", "actual", "actual5d"], $service);

            if ($line['param_name']) {
                $line['description'] = str_replace('%', '<i>' . $line['param_value'] . '</i>', $line['description']);
            }
            $line['amount'] = (double)$line['amount'];
            $line['price'] = (double)$line['price'];

            $ret[] = $line;
        }

        return $ret;
    }

    public static function getRegionList($clientId)
    {
        $account = ClientAccount::findOne(["id" => $clientId]);

        if (!$account) {
            return [];
        }

        $ret = [];
        foreach (NewBill::find_by_sql("
            SELECT
                *
            FROM
                `regions`
            WHERE 
                country_id = '" . $account->country_id . "'
                AND is_active = 1
            ORDER BY
                id>97 DESC, `name`
            ") as $service) {
            $line = self::_exportModelRow(["id", "name", "short_name", "code"], $service);
            $line['voip_prefix'] = ClientCS::getVoipPrefix($line['id']);
            $ret[] = $line;
        }
        return $ret;
    }

    public static function getCollocationTarifs()
    {
        return self::_getInternetTarifs("C");
    }

    public static function getInternetTarifs()
    {
        return self::_getInternetTarifs("I");
    }

    public static function getDomainTarifs($currency = 'RUB', $status = 'public', $code = 'uspd')
    {
        $ret = [];
        foreach (NewBill::find_by_sql("
            SELECT
                id, description, period, price
            FROM
                `tarifs_extra`
            WHERE
                `currency` = ?
            AND `status` = ?
            AND `code` = ?
            AND `description` LIKE('" . 'Хостинг\_' . "%')
            ORDER BY
                `id`
            ", [$currency, $status, $code]) as $service) {
            $line = self::_exportModelRow(["id", "description", "period", "price"], $service);
            $line['price'] = (double)round($line['price']);
            $line['description'] = str_replace('Хостинг_', '', $line['description']);
            $ret[] = $line;
        }
        return $ret;
    }

    public static function getVpbxTarifs($accountId = 0)
    {
        $currency = "RUB";
        $status = "public";

        $account = ClientAccount::findOne(["id" => $accountId]);
        if ($account) {
            $currency = $account->currency;
        }

        return self::_getVpbxTarifs($currency, $status);

    }

    public static function _getVpbxTarifs($currency = 'RUB', $status = 'public')
    {
        $ret = [];
        foreach (NewBill::find_by_sql("
            SELECT
                *
            FROM
                `tarifs_virtpbx`
            WHERE
                `currency` = ?
            AND `status` = ?
            ORDER BY
                `id`
            ", [$currency, $status]) as $service) {
            $line = self::_exportModelRow(
                [
                    'id',
                    'description',
                    'period',
                    'price',
                    'num_ports',
                    'overrun_per_port',
                    'space',
                    'overrun_per_gb',
                    'ext_did_count',
                    'ext_did_monthly_payment',
                    'is_record',
                    'is_web_call',
                    'is_fax'
                ],
                $service
            );
            $line["price"] = number_format($line["price"], 2, ".", "");
            $ret[] = $line;
        }
        return $ret;
    }

    /**
     * Дерево тарифов по телефонии
     *
     * @param integer $accountId
     * @return array
     */
    public static function getVoipTarifs($accountId)
    {
        $account = self::getAccount($accountId);

        if (!$account) {
            throw new InvalidArgumentException("account_not_found");
        }

        return TariffVoip::find()
            ->select(['id', 'name', 'month_line', 'month_number', 'once_line', 'once_number', 'freemin_for_number', 'connection_point_id'])
            ->addSelect(['free_local_min' => new Expression("IF(free_local_min < 5000, free_local_min, '')")])
            ->where([
                'status' => TariffVoip::STATUS_PUBLIC,
                'currency_id' => $account->currency,
                'ndc_type_id' => NdcType::ID_GEOGRAPHIC,
                'dest' => 4
            ])
            ->orderBy(['name' => SORT_ASC])
            ->asArray()
            ->all();
    }

    public static function getFreeNumbers($clientId, $cityId, $didGroupId, $isSimple = false)
    {
        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(['id' => $clientId]);
        Assert::isObject($account);

        $didGroup = DidGroup::findOne(['id' => $didGroupId]);
        Assert::isObject($didGroup);

        $ret = [];

        /** @var FreeNumberFilter $numbers */
        $numbers = (new FreeNumberFilter)
            ->setIsService(false)
            ->setDidGroup($didGroup->id)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL);

        if ($cityId == Number::TYPE_7800) {
            $numbers
                ->setNdcType(NdcType::ID_FREEPHONE)
                ->setCountry($account->contragent->country_id);
        } else {
            $numbers
                ->setNdcType(NdcType::ID_GEOGRAPHIC)
                ->setCity($cityId);
        }

        $countryPrefix = null;
        $cityPostfixLength = null;

        foreach ($numbers->result() as $number) {

            if (!$countryPrefix) {
                $countryPrefix = $number->country->prefix;
                $cityPostfixLength = $cityId == Number::TYPE_7800 ? NumberBeautyDao::DEFAULT_POSTFIX_LENGTH : $number->city->postfix_length;
            }

            $line = [
                'full_number' => $number->number,
            ];


            $line['number'] = substr($number->number, -$cityPostfixLength);
            $line['area_code'] = substr($number->number, strlen($countryPrefix),
                strlen($number->number) - strlen($countryPrefix) - $cityPostfixLength);

            $number = $line['number'];

            // через каждые 2 знака тире. Формат 22-33-44 или 222-33-44
            $tmp = "";
            do {
                $tmp = "-" . substr($number, -2) . $tmp;
                $number = substr($number, 0, strlen($number) - 2);
            } while (strlen($number) > 3);

            $line['number'] = $number . $tmp;

            $ret[] = $isSimple ? $number : $line;
        }

        return $ret;
    }


    public static function orderInternetTarif($client_id, $region_id, $tarif_id)
    {
        $order_str = 'Заказ услуги Интернет из Личного Кабинета. ' .
            'Client ID: ' . $client_id . '; Region ID: ' . $region_id . '; Tarif ID: ' . $tarif_id;

        return ['status' => 'error', 'message' => 'order_error'];
    }

    public static function orderCollocationTarif($client_id, $region_id, $tarif_id)
    {
        $order_str = 'Заказ услуги Collocation из Личного Кабинета. ' .
            'Client ID: ' . $client_id . '; Region ID: ' . $region_id . '; Tarif ID: ' . $tarif_id;

        return ['status' => 'error', 'message' => 'order_error'];
    }

    /**
     * Заказ услуги по телефонии
     *
     * @param integer $clientId
     * @param string $did
     * @return array
     */
    public static function orderVoip($clientId, $did)
    {
        try {
            $clientAccount = ClientAccount::findOne($clientId);
            Assert::isObject($clientAccount);

            $number = Number::findOne($did);
            Assert::isObject($number);
            if ($number->status != Number::STATUS_INSTOCK) {
                return [
                    'status' => 'error',
                    'message' => 'voip_number_not_free'
                ];
            }

            $mainTariff = TariffVoip::findOne([
                'status' => TariffVoip::STATUS_TEST,
                'connection_point_id' => $number->city->connection_point_id
            ]);

            Assert::isObject($mainTariff);
            Assert::isEqual($clientAccount->currency, $mainTariff->currency_id);
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return [
                'status' => 'error',
                'message' => 'order_validate_data_error',
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $model = new UsageVoipEditForm();
            $model->scenario = 'add';
            $model->initModel($clientAccount);

            $model->tariff_main_id = $mainTariff->id;
            $model->no_of_lines = 1;
            $model->did = $number->number;

            $model->prepareAdd();

            if (!$model->validate()) {
                Yii::error($model->errors);
                throw new LogicException('Model not valid');
            }

            $model->add();
            $usageId = $model->id;

            $message = "Заказ услуги IP телефония из Личного кабинета. \n";
            $message .= 'Клиент: ' . $clientAccount->company . " (Id: " . $clientAccount->id . ")\n";
            $message .= 'Город: ' . $number->city->name . "\n";
            $message .= 'Номер: ' . $number->number . "\n";
            $message .= 'Тарифный план: ' . $mainTariff->name;


            if (!self::createTT($message, $clientAccount->client, self::_getUserForTrouble($clientAccount->contract->account_manager),
                'usage_voip', $usageId)
            ) {
                throw new LogicException('Creating trouble error in order voip');
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage() . PHP_EOL . print_r($e->getTrace(), true));
            return [
                'status' => 'error',
                'message' => 'order_error'
            ];
        }

        return [
            'status' => 'ok',
            'message' => 'order_ok'
        ];
    }

    /**
     * @param $clientId - ID клиента в Стат
     * @param $regionId - ID региона
     * @param $tariffId - ID тарифа
     * @return array
     * @throws Exception
     */
    public static function orderVpbxTarif($clientId, $regionId, $tariffId)
    {
        $clientId = (int)$clientId;
        $regionId = (int)$regionId;
        $tariffId = (int)$tariffId;

        $account = ClientAccount::findOne(["id" => $clientId]);

        if (!$account) {
            throw new Exception("data_error");
        }

        if (!$regionId) {
            $regionId = $account->region;
        }

        $region = Region::findOne(['id' => $regionId]);

        if (!$region) {
            throw new Exception("data_error");
        }

        $tariff = TariffVirtpbx::findOne(['id' => $tariffId]);

        if (!$tariff) {
            throw new Exception("data_error");
        }

        $message = "Заказ услуги Виртуальная АТС из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $clientId)\n";
        $message .= 'Регион: ' . $region->name . "\n";
        $message .= 'Тарифный план: ' . $tariff->description;

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $vats = new UsageVirtpbx();
            $vats->client = $account->client;
            $vats->actual_from = (new DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT);
            $vats->actual_to = UsageInterface::MAX_POSSIBLE_DATE;
            $vats->amount = 1;
            $vats->status = UsageInterface::STATUS_CONNECTING;
            $vats->region = $regionId;

            if (!$vats->save()) {
                throw new ModelValidationException($vats);
            }

            $logTariff = new LogTarif();
            $logTariff->service = UsageVirtpbx::tableName();
            $logTariff->id_service = $vats->id;
            $logTariff->id_tarif = $tariffId;
            $logTariff->ts = (new DateTime('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $logTariff->date_activation = (new DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT);
            $logTariff->id_user = self::_getUserLK();
            $logTariff->save();

            if (!self::createTT($message, $account->client,
                    self::_getUserForTrouble($account->contract->account_manager)) > 0
            ) {
                throw new LogicException('Ошибка создания заявки на создание ВАТС');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error("[lk order] " . $e->getMessage() . PHP_EOL . print_r($e->getTrace(), true));
            return [
                'status' => 'error',
                'message' => 'service_connecting_error'
            ];
        }

        $transaction->commit();

        return [
            'status' => 'ok',
            'message' => 'order_ok'
        ];
    }

    public static function orderDomainTarif($client_id, $region_id, $tarif_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $region = Region::findOne($region_id);
        $tarif = $db->GetValue("select description from tarifs_extra where id='" . $tarif_id . "'");

        $message = "Заказ услуги Домен из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Регион: ' . $region->namename . "\n";
        $message .= 'Тарифный план: ' . $tarif;

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function orderEmail($client_id, $domain_id, $local_part, $password)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $domain = $db->GetValue("select domain from domains where id='" . $domain_id . "'");
        $email_id = $db->GetValue("
                select id from emails where 
                    client='" . $account->client . "' and
                    domain='" . $domain . "' and
                    local_part='" . $local_part . "'
                ");
        if ($email_id) {
            return ['status' => 'error', 'message' => 'email_already_used'];
        }

        $db->QueryInsert("emails", [
                "local_part" => $local_part,
                "domain" => $domain,
                "password" => $password,
                "client" => $account->client,
                "box_size" => "20",
                "box_quota" => "50000",
                "status" => "working",
                "actual_from" => ['NOW()'],
                "actual_to" => "4000-01-01"
            ]
        );
        return ['status' => 'ok', 'message' => 'email_added'];
    }

    public static function changeInternetTarif($client_id, $service_id, $tarif_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $address = $db->GetValue("select address from usage_ip_ports where id='" . $service_id . "'");
        $tarif = $db->GetValue("select name from tarifs_internet where id='" . $tarif_id . "'");

        $message = "Заказ изменения тарифного плана услуги Интернет из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Адрес: ' . $address . "\n";
        $message .= 'Тарифный план: ' . $tarif;

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function changeCollocationTarif($client_id, $service_id, $tarif_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $address = $db->GetValue("select address from usage_ip_ports where id='" . $service_id . "'");
        $tarif = $db->GetValue("select name from tarifs_voip where id='" . $tarif_id . "'");

        $message = "Заказ изменения тарифного плана услуги Collocation из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Адрес: ' . $address . "\n";
        $message .= 'Тарифный план: ' . $tarif;

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function changeVoipTarif($client_id, $service_id, $tarif_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $voip = $db->GetRow("select E164, status, actual_from from usage_voip where id='" . $service_id . "' AND client='" . $account["client"] . "'");
        $tarif = $db->GetValue("select name from tarifs_voip where id='" . $tarif_id . "'");

        $message = "Заказ изменения тарифного плана услуги IP Телефония из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Номер: ' . $voip['E164'] . "\n";
        $message .= 'Тарифный план: ' . $tarif;

        if ($voip['actual_from'] > '3000-01-01') {
            $db->QueryUpdate("log_tarif", ["id_service", "service"], ["service" => "usage_voip", "id_service" => $service_id, "id_tarif" => $tarif_id]);
            $message .= "\n\nтариф сменен, т.к. подключения не было";
        }
        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function changeVpbxTarif($client_id, $service_id, $tarif_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $tarif = $db->GetValue("select description as name from tarifs_virtpbx where id='" . $tarif_id . "'");

        if (!$account || !$tarif) {
            throw new Exception("data_error");
        }

        $message = "Заказ изменения тарифного плана услуги Виртуальная АТС из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Новый тарифный план: ' . $tarif;

        $vpbx = $db->GetRow($q = "select * from usage_virtpbx where id=" . $service_id . " and client = '" . $account["client"] . "'");

        if ($vpbx) {
            $first_day_next_month = date('Y-m-d', mktime(0, 0, 0, date("m") + 1, 1, date("Y")));
            $db->QueryInsert("log_tarif", [
                    "service" => 'usage_virtpbx',
                    "id_service" => $service_id,
                    "id_tarif" => $tarif_id,
                    "id_user" => self::_getUserLK(),
                    "ts" => ['NOW()'],
                    "date_activation" => ($vpbx['actual_from'] > '3000-01-01') ? date('Y-m-d') : $first_day_next_month
                ]
            );

            $message .= "\n\nтариф изменен из личного кабинета";

            if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
                return ['status' => 'ok', 'message' => 'order_ok'];
            }
        }


        return ['status' => 'error', 'message' => 'order_error'];
    }

    public static function changeDomainTarif($client_id, $service_id, $tarif_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $domain = $db->GetValue("select domain from domains where id='" . $service_id . "'");
        $tarif = $db->GetValue("select description from tarifs_extra where id='" . $tarif_id . "'");

        $message = "Заказ на изменение услуги Домен из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Домен: ' . $domain . "\n";
        $message .= 'Тарифный план: ' . $tarif;

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function changeEmail($client_id, $email_id, $password)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $email = $db->GetRow("select * from emails where client='" . $account->client . "' and id=" . $email_id);
        if ($email) {
            $db->QueryUpdate(
                "emails",
                ["id", "client"],
                ["id" => $email_id, "client" => $account->client, "password" => $password]
            );

            return ['status' => 'ok', 'message' => 'password_changed'];
        } else {
            return ['status' => 'error', 'message' => 'password_changed_error'];
        }
    }

    public static function changeEmailSpamAct($client_id, $email_id, $spam_act)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $cur_spam_act = $db->GetValue("select spam_act from emails where client='" . $account->client . "' and id=" . $email_id);
        if ($cur_spam_act) {
            $db->QueryUpdate("emails", ["id", "client"], ["id" => $email_id, "client" => $account->client, "spam_act" => $spam_act]);
        } else {
            return ['status' => 'error', 'message' => 'email_spam_filter_change_error'];
        }

        return ['status' => 'ok', 'message' => 'ok'];
    }

    public static function getEmailAccess($client_id)
    {
        global $db;

        $res = ['add_email' => 0, 'domain_cnt' => 0];
        $clients = [780, 2339, 2817, 3680, 3920, 1378, 447, 1266, 652, 41, 941, 51, 440, 54, 452, 866, 529, 36678];

        /*если клиент не в заданном списке - вернем пустой массив*/
        if (in_array($client_id, $clients)) {
            $res['add_email'] = 1;
        }

        $res['domain_cnt'] = $db->GetValue("
                SELECT 
                    COUNT(1)
                FROM 
                    `domains` AS `d` 
                INNER JOIN `clients` ON (`d`.`client` = `clients`.`client`) 
                WHERE `clients`.`id`=" . $client_id);

        return $res;
    }

    public static function disconnectInternet($client_id, $service_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $address = $db->GetValue("select address from usage_ip_ports where id='" . $service_id . "'");

        $message = "Заказ на отключение услуги Интернет из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Адрес: ' . $address;

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function disconnectCollocation($client_id, $service_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $address = $db->GetValue("select address from usage_ip_ports where id='" . $service_id . "'");

        $message = "Заказ на отключение услуги Collocation из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Адрес: ' . $address;

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function disconnectVoip($client_id, $service_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $voip = $db->GetRow("select E164, status, actual_from from usage_voip where id='" . $service_id . "' AND client='" . $account["client"] . "'");

        $message = "Заказ на отключение услуги IP Телефония из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Номер: ' . $voip['E164'];

        if ($voip['actual_from'] > '3000-01-01') {
            $db->QueryDelete('log_tarif', ["service" => "usage_voip", 'id_service' => $service_id]);
            $db->QueryDelete('usage_voip', ['id' => $service_id]);
            $message .= "\n\nномер удален, т.к. подключения не было";
        }

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }
    }

    public static function disconnectVpbx($client_id, $service_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);

        $message = "Заказ на отключение услуги Виртуальная АТС из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Услуга: ' . $service_id . " (Id: $service_id)\n";

        $vpbx = $db->GetRow($q = "select id, actual_from from usage_virtpbx where id=" . $service_id . " and client = '" . $account["client"] . "'");

        if ($vpbx) {
            if ($vpbx["actual_from"] > "3000-01-01") {
                $db->QueryDelete("log_tarif", ["service" => "usage_virtpbx", "id_service" => $vpbx["id"]]);
                $db->QueryDelete("usage_virtpbx", ["id" => $vpbx["id"]]);

                $message .= "\n\nВиртуальная АТС отключена автоматически, т.к. подключения не было";
            }

            if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
                return ['status' => 'ok', 'message' => 'order_ok'];
            }
        }

        return ['status' => 'error', 'message' => 'order_error'];
    }

    public static function disconnectDomain($client_id, $service_id)
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $domain = $db->GetRow("select domain from domains where id='" . $service_id . "'");

        $message = "Заказ на отключение услуги Домен из Личного Кабинета. \n";
        $message .= 'Клиент: ' . $account->contract->contragent->name . " (Id: $client_id)\n";
        $message .= 'Домен: ' . $domain;

        if (self::createTT($message, $account->client, self::_getUserForTrouble($account->contract->account_manager)) > 0) {
            return ['status' => 'ok', 'message' => 'order_ok'];
        } else {
            return ['status' => 'error', 'message' => 'order_error'];
        }

    }

    public static function disconnectEmail($client_id, $email_id, $action = 'disable')
    {
        global $db;

        $account = ClientAccount::findOne($client_id);
        $email = $db->GetRow("select * from emails where client='" . $account->client . "' and id=" . $email_id);
        if ($email) {
            $db->QueryUpdate(
                "emails",
                ["id", "client"],
                [
                    "id" => $email_id,
                    "client" => $account->client,
                    "enabled" => (($action == 'disable') ? '0' : '1'),
                    "actual_to" => (($action == 'disable') ? ['NOW()'] : '4000-01-01')
                ]
            );
            return ['status' => 'ok', 'message' => (($action == 'disable') ? 'email_off' : 'email_on')];
        } else {
            return ['status' => 'error', 'message' => (($action == 'disable') ? 'email_off_error' : 'email_on_error')];
        }
    }

    /**
     * Получение карточки клиента
     *
     */
    public static function getAccountData($client_id = '')
    {
        if (is_array($client_id) || !$client_id || !preg_match("/^\d{1,6}$/", $client_id)) {
            throw new Exception("account_is_bad");
        }

        $account = self::getAccount($client_id);
        $ret = [
            'id' => $account->id,
            'client' => $account->client,
            'status' => $account->status,
            'inn' => $account->inn,
            'kpp' => $account->kpp,
            'address_post' => $account->address_post,
            'address_post_real' => $account->address_post_real,
            'corr_acc' => $account->corr_acc,
            'pay_acc' => $account->pay_acc,
            'bik' => $account->bik,
            'mail_who' => $account->mail_who,
            'address_connect' => $account->address_connect,
            'phone_connect' => $account->phone_connect,
            'address_jur' => $account->contract->contragent->address_jur,
            'signer_name' => $account->contract->contragent->fio,
            'signer_position' => $account->contract->contragent->position,
            'company' => $account->contract->contragent->name,
            'company_full' => $account->contract->contragent->name_full,
        ];

        return $ret;
    }

    /**
     * Сохранение карточки клиента
     *
     */
    public static function saveClientData($client_id = '', $data = [])
    {
        global $db;
        $status_arr = ['income', 'connecting', 'testing'];
        $edit_fields = [
            'inn',
            'kpp',
            'company_full',
            'address_jur',
            'address_post_real',
            'pay_acc',
            'bik',
            'signer_name',
            'signer_position',
            'mail_who',
            'address_connect',
            'phone_connect'
        ];

        if (is_array($client_id) || !$client_id || !preg_match("/^\d{1,6}$/", $client_id)) {
            throw new Exception("account_is_bad");
        }

        $account = self::getAccount($client_id);
        if (!$account) {
            throw new Exception("account_is_bad");
        }

        if (!in_array($account['status'], $status_arr)) {
            throw new Exception("account_edit_ban");
        }

        $edit_data = ['id' => $client_id];
        foreach ($edit_fields as $fld) {
            if (isset($data[$fld])) {
                $v = htmlentities(trim(strip_tags(preg_replace(['/\\\\+/', '/\/\/+/'], ['\\', '/'], $data[$fld]))), ENT_QUOTES);
                $edit_data[$fld] = substr($v, 0, 250);
            }
        }

        $res = $db->QueryUpdate('clients', 'id', self::_importModelRow($edit_data));

        return $res;
    }

    /**
     * Получение названия компании
     *
     */
    public static function getCompanyName($client_id = '')
    {
        if (is_array($client_id) || !$client_id || !preg_match("/^\d{1,6}$/", $client_id)) {
            throw new Exception("account_is_bad");
        }

        $ret = ['name' => self::getAccount($client_id)->superClient->name];
        return $ret;
    }

    public static function getStatisticsVoipPhones($client_id = '')
    {
        if (is_array($client_id) || !$client_id || !preg_match("/^\d{1,6}$/", $client_id)) {
            throw new LogicException("account_is_bad");
        }

        $account = self::getAccount($client_id);

        if (!$account) {
            throw new LogicException("account_is_bad");
        }

        $usagesData = ReportUsageDao::me()->getUsageVoipAndTrunks($account);

        $phones = ReportUsageDao::me()->usagesToSelect($usagesData, true);
        array_walk($phones, function (&$item, $key) {
            $item = ['id' => $key, 'number' => $item];
        });

        return [
            'phones' => array_values($phones),
            'timezones' => ReportUsageDao::me()->getTimezones($account, $usagesData['voip'])
        ];
    }

    public static function getStatisticsVoipData($client_id = '', $phone = 'all', $from = '', $to = '', $detality = 'day', $destination = 'all', $direction = 'both', $timezone = 'Europe/Moscow', $onlypay = 0, $isFull = 0)
    {
        $destination = (!in_array($destination, ['all', '0', '0-m', '0-f', '0-f-z', '1', '1-m', '1-f', '2', '3'])) ? 'all' : $destination;
        $direction = (!in_array($direction, ['both', 'in', 'out'])) ? 'both' : $direction;

        $account = self::getAccount($client_id);

        if (!$account) {
            throw new LogicException("account_is_bad");
        }

        /** @var ReportUsageDao $reportDao */
        $reportDao = ReportUsageDao::me();

        $usagesData = $reportDao->getUsageVoipAndTrunks($account);
        list($usageIds, $regions, $isTrunk) = $reportDao->reportConfig($phone, $usagesData);

        $stats = [];

        if ($usageIds) {
            $stats = $reportDao->getUsageVoipStatistic(
                ($isTrunk ? 'trunk' : $regions),
                strtotime($from),
                strtotime($to),
                $detality,
                $account->id,
                $usageIds,
                $onlypay,
                $destination,
                $direction,
                $isFull,
                $packages = [],
                $timezone
            );
        }

        return $stats;
    }

    public static function getStatisticsInternetRoutes($client_id = '')
    {
        include PATH_TO_ROOT . "modules/stats/module.php";
        $module_stats = new m_stats();

        $account = self::getAccount($client_id);

        list($routes_all, $routes_allB) = $module_stats->get_routes_list($account->client);

        return $routes_all;
    }

    public static function getStatisticsInternetData(
        $client_id = '',
        $from = '',
        $to = '',
        $detality = 'day',
        $route = '',
        $is_coll = 0
    ) {

        include PATH_TO_ROOT . "modules/stats/module.php";

        $module_stats = new m_stats();

        $account = self::getAccount($client_id);

        list($routes_all, $routes_allB) = $module_stats->get_routes_list($account->client);

        $from = strtotime($from);
        $to = strtotime($to);

        //если сеть не задана, выводим все подсети клиента.
        $routes = [];

        if ($route) {
            if (isset($routes_all[$route])) {
                $routes = [$routes_all[$route]];
            } else {
                return [];
            }
        } else {
            $routes = [];
            foreach ($routes_allB as $r) {
                $routes[] = $r;
            }
        }

        $stats = $module_stats->GetStatsInternet($from, $to, $detality, $routes, $is_coll);

        return $stats;
    }

    public static function getStatisticsCollocationData($client_id = '', $from = '', $to = '', $detality = 'day', $route = '')
    {
        return self::getStatisticsInternetData($client_id, $from, $to, $detality, $route, 1);
    }

    public static function getServiceOptions($service, $clientId)
    {
        $o = new LkServiceOptions($service, $clientId);
        return $o->getOptions();
    }

    /**
     * Получение настроек уведомлений клиента
     *
     * @param int $client_id id клиента
     */
    public static function getAccountsNotification($client_id = '')
    {
        if (!self::validateClient($client_id)) {
            throw new Exception("account_is_bad");
        }

        $ret = [];
        foreach (\ClientContact::find_by_sql("
                select c.id, c.type, c.data as info, n.min_balance, n.min_day_limit as day_limit, n.add_pay_notif, n.status
                from client_contacts c
                left join lk_notice_settings n on n.client_contact_id=c.id
                left join user_users u on u.id=c.user_id
                where c.client_id='" . $client_id . "'
                and u.user='AutoLK'
                ") as $v) {
            $ret[] = self::_exportModelRow([
                'id',
                'type',
                'info',
                'min_balance',
                'day_limit',
                'add_pay_notif',
                'status'
            ], $v);
        }
        return $ret;
    }

    /**
     * Добавление контакта для уведомлений
     *
     * @param int|null $client_id id клиента
     * @param string $type тип (телефон или Email)
     * @param string $data значение
     * @param string $lang язык
     * @return array
     */
    public static function addAccountNotification($client_id = null, $type = '', $data = '', $lang = LanguageModel::LANGUAGE_DEFAULT)
    {
        $data = trim($data);

        if (!($account = self::validateClient($client_id))) {
            return [
                'status' => 'error',
                'message' => 'account_is_bad'
            ];
        }

        $contactsCount = ClientContact::find()->where([
            'client_id' => $client_id,
            'user_id' => User::LK_USER_ID
        ])->count();

        if ($contactsCount > self::MAX_LK_NOTIFICATION_CONTACTS) {
            return [
                'status' => 'error',
                'message' => 'contact_max_length'
            ];
        }

        if (!in_array($type, ['email', 'phone'])) {
            return [
                'status' => 'error',
                'message' => 'contact_type_error'
            ];
        }
        if (!self::validateData($type, $data)) {
            return [
                'status' => 'error',
                'message' => 'format_error'
            ];
        }

        $attrs = [
            'client_id' => $client_id,
            'type' => $type,
            'data' => $data,
            'user_id' => User::LK_USER_ID
        ];

        $contact = ClientContact::findOne($attrs);

        if (!$contact) {
            $contact = new ClientContact();
            $contact->setAttributes($attrs);

            if (!$contact->validate()) {
                return [
                    'status' => 'error',
                    'message' => 'format_error'
                ];
            }

            if (!$contact->save()) {
                return [
                    'status' => 'error',
                    'message' => 'contact_add_error'
                ];
            }
        }

        $lkNoticeAttrs = [
            'client_contact_id' => $contact->id,
            'client_id' => $contact->client_id
        ];

        $lkNotice = LkNoticeSetting::findOne($lkNoticeAttrs);

        if (!$lkNotice) {
            $lkNotice = new LkNoticeSetting;
            $lkNotice->setAttributes($lkNoticeAttrs, false);

            if (!$lkNotice->save()) {
                return [
                    'status' => 'error',
                    'message' => 'contact_add_error'
                ];
            }

            self::sendApproveMessage($client_id, $type, $data, $contact->id, $lang);
        }

        return [
            'status' => 'ok',
            'message' => 'contact_add_ok'
        ];
    }

    /**
     * Валидация контактной информации
     *
     * @param string $type тип контакта
     * @param string $data данные контакты
     * @return bool
     */
    public static function validateData($type = '', $data = '')
    {
        switch ($type) {
            case 'email':
                return (bool)filter_var($data, FILTER_VALIDATE_EMAIL);
                break;
            case 'phone':
                return (bool)preg_match("/^\s*\+?[0-9\- ]{7,15}\s*$/", $data);
                break;
        }
        return false;
    }

    /**
     * Удаление контакта для уведомлений
     *
     * @param int $client_id id клиента
     * @param int $contact_id id контакта
     */
    public static function disableAccountNotification($client_id = '', $contact_id = '')
    {
        if (!self::validateClient($client_id)) {
            return [
                'status' => 'error',
                'message' => 'account_is_bad'
            ];
        }

        if (!self::validateContact($client_id, $contact_id)) {
            return [
                'status' => 'error',
                'message' => 'contact_id_error'
            ];
        }

        LkNoticeSetting::deleteAll([
            'client_contact_id' => $contact_id
        ]);

        ClientContact::deleteAll([
            'id' => $contact_id,
            'client_id' => $client_id
        ]);


        return [
            'status' => 'ok',
            'message' => 'contact_del_ok'
        ];
    }

    /**
     * Активация контакта для уведомлений
     *
     * @param int $client_id id клиента
     * @param int $contact_id id контакта
     * @param string $code код активации
     */
    public static function activateAccountNotification($client_id = '', $contact_id = '', $code = '')
    {
        if (!self::validateClient($client_id)) {
            return [
                'status' => 'error',
                'message' => 'account_is_bad'
            ];
        }

        $contract = self::validateContact($client_id, $contact_id);

        if (!$contract) {
            return [
                'status' => 'error',
                'message' => 'contact_id_error'
            ];
        }

        if ($code == '') {
            return [
                'status' => 'error',
                'message' => 'contact_activation_code_empty'
            ];
        }

        $settings = LkNoticeSetting::findOne([
            'client_id' => $client_id,
            'client_contact_id' => $contact_id
        ]);

        if (!$settings) {
            return [
                'status' => 'error',
                'message' => 'contact_activation_error'
            ];
        }

        if ($settings->activate_code != $code) {
            return [
                'status' => 'error',
                'message' => 'contact_activation_code_bad'
            ];
        }

        $settings->status = LkNoticeSetting::STATUS_WORK;

        if (!$settings->save()) {
            return [
                'status' => 'error',
                'message' => 'contact_activation_error'
            ];
        }

        return [
            'status' => 'ok',
            'message' => 'contact_activation_ok'
        ];
    }

    /**
     * Активация Email контакта для уведомлений
     *
     * @param int $client_id id клиента
     * @param int $contact_id id контакта
     * @param string $key ключ
     */
    public static function activatebyemailAccountNotification($client_id = '', $contact_id = '', $key = '')
    {
        global $db;
        if (!self::validateClient($client_id)) {
            return ['status' => 'error', 'message' => 'account_is_bad'];
        }
        if (!self::validateContact($client_id, $contact_id)) {
            return ['status' => 'error', 'message' => 'contact_id_error'];
        }

        if ($key == '' || $key != md5($client_id . 'SeCrEt-KeY' . $contact_id)) {
            return ['status' => 'error', 'message' => 'contact_activation_code_bad'];
        }

        $res = LkNoticeSetting::updateAll([
            'status' => LkNoticeSetting::STATUS_WORK,
        ], [
            'client_id' => $client_id,
            'client_contact_id' => $contact_id,
        ]);
        if ($res) {
            return ['status' => 'ok', 'message' => 'contact_activation_ok'];
        }

        return ['status' => 'error', 'message' => 'contact_activation_error'];
    }

    /**
     * Сохранение настроек уведомлений
     *
     * @param string $client_id
     * @param array $data
     * @param int $minBalance
     * @param int $minDayLimit
     * @return array
     */
    public static function saveAccountNotification(
        $client_id = '',
        $data = [],
        $minBalance = 0,
        $minDayLimit = 0
    ) {
        global $db;

        if (!($account = self::validateClient($client_id))) {
            return [
                'status' => 'error',
                'message' => 'account_is_bad'
            ];
        }

        $minDayRule = ['min_day_limit', 'integer'];

        if (!in_array($account->contract->business_id, [\app\models\Business::OTT, \app\models\Business::OPERATOR, \app\models\Business::PROVIDER])) {
            $minDayRule['min'] = 0;
        }

        $model = \app\classes\DynamicModel::validateData([
            'min_balance' => $minBalance,
            'min_day_limit' => $minDayLimit
        ], [
            ['min_balance', 'integer'],
            $minDayRule
        ]);

        if (!$model->validate()) {
            return [
                'status' => 'error',
                'message' => 'format_error', //Неверный формат данных.
            ];
        }

        $res = [];
        foreach ($data as $name) {
            $tmp = explode('__', $name);

            if (!isset($res[$tmp[1]])) {
                $res[$tmp[1]] = [
                    'client_contact_id' => $tmp[1],
                    ImportantEventsNames::MIN_BALANCE => 0,
                    ImportantEventsNames::MIN_DAY_LIMIT => 0,
                    ImportantEventsNames::ADD_PAY_NOTIF => 0,
                ];
            }

            $res[$tmp[1]][$tmp[0]] = 1;
        }

        $contacts = ClientContact::findAll([
            'client_id' => $client_id,
            'user_id' => new Expression('(SELECT id FROM user_users WHERE user="AutoLK")'),
        ]);

        foreach ($contacts as $contact) {
            $noticeSettings = LkNoticeSetting::findOne([
                'client_contact_id' => $contact->id,
                'client_id' => $client_id,
            ]);

            if (is_null($noticeSettings)) {
                $noticeSettings = new LkNoticeSetting;
            }

            $noticeSettings->setAttribute(
                ImportantEventsNames::MIN_BALANCE,
                isset($res[$contact->id]) ? $res[$contact->id][ImportantEventsNames::MIN_BALANCE] : 0
            );
            $noticeSettings->setAttribute(
                ImportantEventsNames::MIN_DAY_LIMIT,
                isset($res[$contact->id]) ? $res[$contact->id][ImportantEventsNames::MIN_DAY_LIMIT] : 0
            );
            $noticeSettings->setAttribute(
                ImportantEventsNames::ADD_PAY_NOTIF,
                isset($res[$contact->id]) ? $res[$contact->id][ImportantEventsNames::ADD_PAY_NOTIF] : 0
            );

            $noticeSettings->save();
        }

        $clientSettings = LkClientSettings::findOne(['client_id' => $client_id]);

        if (is_null($clientSettings)) {
            $clientSettings = new LkClientSettings;
            $clientSettings->client_id = $client_id;
        }

        $clientSettings->{ImportantEventsNames::MIN_BALANCE} = (int)$minBalance;
        $clientSettings->{ImportantEventsNames::MIN_DAY_LIMIT} = (int)$minDayLimit;

        if (
            $clientSettings->is_min_balance_sent
            &&
            $clientSettings->{ImportantEventsNames::MIN_BALANCE} < $minBalance
        ) {
            $clientSettings->is_min_balance_sent = 0;
        }

        if (
            $clientSettings->is_min_day_limit_sent
            &&
            $clientSettings->{ImportantEventsNames::MIN_DAY_LIMIT} < $minDayLimit
        ) {
            $clientSettings->is_min_day_limit_sent = 0;
        }

        // Логирование изменения значения критического остатка
        if ($clientSettings->isAttributeChanged(ImportantEventsNames::MIN_BALANCE)) {
            ImportantEvents::create(
                $eventName = ImportantEventsNames::CHANGE_CREDIT_LIMIT,
                $eventSource = ImportantEventsSources::SOURCE_LK,
                $eventData = [
                    'client_id' => $client_id,
                    'value' => $minBalance,
                    'before' => $clientSettings->getOldAttribute(ImportantEventsNames::MIN_BALANCE),
                ]
            );
        }

        // Логирование изменения значени суточного лимита
        if ($clientSettings->isAttributeChanged(ImportantEventsNames::MIN_DAY_LIMIT)) {
            ImportantEvents::create(
                $eventName = ImportantEventsNames::CHANGE_MIN_DAY_LIMIT,
                $eventSource = ImportantEventsSources::SOURCE_LK,
                $eventData = [
                    'client_id' => $client_id,
                    'value' => $minDayLimit,
                    'before' => $clientSettings->getOldAttribute(ImportantEventsNames::MIN_DAY_LIMIT),
                ]
            );
        }

        if ($clientSettings->save()) {
            return [
                'status' => 'ok',
                'message' => 'save_ok'
            ];
        }

        return [
            'status' => 'error',
            'message' => 'data_error',
        ];
    }

    /**
     * Получение настроек клиента
     *
     * @param int $client_id id клиента
     */
    public static function getAccountSettings($client_id = '')
    {
        if (!self::validateClient($client_id)) {
            throw new Exception("account_is_bad");
        }

        $ret = [];
        foreach (\ClientContact::find_by_sql("
                select *, min_day_limit as day_limit
                from lk_client_settings
                where client_id='" . $client_id . "'
                ") as $v) {
            $ret['client_id'] = (int)$v->client_id;
            $ret['min_balance'] = (float)$v->min_balance;
            $ret['day_limit'] = (float)$v->day_limit;
        }
        return $ret;
    }

    public static function sendApproveMessage($client_id, $type, $data, $contact_id, $lang = LanguageModel::LANGUAGE_DEFAULT)
    {
        global $design, $db;

        $clientAccount = \app\models\ClientAccount::findOne($client_id);
        $language = Language::normalizeLang($lang);

        if ($type == 'email') {
            $key = md5($client_id . 'SeCrEt-KeY' . $contact_id);

            LkNoticeSetting::updateAll([
                'activate_code' => $key,
            ], [
                'client_contact_id' => $contact_id,
                'client_id' => $client_id,
            ]);

            $assigns = [
                'url' =>
                    'https://' .
                    Yii::t('settings', 'lk_domain', [], $language) .
                    '/lk/accounts_notification/activate_by_email?' .
                    'client_id=' . $client_id .
                    '&contact_id=' . $contact_id .
                    '&key=' . $key,
                'organization' => $clientAccount->organization->name,
            ];

            $design->assign($assigns);
            $message = $design->fetch('letters/notification/' . $language . '/email/approve.tpl');

            $params = [
                'data' => $data,
                'subject' => Yii::t('settings', 'email_subject_approve', [], $language),
                'message' => $message,
                'type' => 'email',
                'contact_id' => $contact_id,
                'lang' => $language,
            ];

            $id = $db->QueryInsert('lk_notice', $params);

            if ($id) {
                return true;
            }
        } else {
            if ($type == 'phone') {
                $code = '';
                for ($i = 0; $i < 6; $i++) {
                    $code .= mt_rand(0, 9);
                }

                LkNoticeSetting::updateAll([
                    'activate_code' => $code,
                ], [
                    'client_contact_id' => $contact_id,
                    'client_id' => $client_id,
                ]);

                $params = [
                    'data' => $data,
                    'message' => 'Код активации: ' . $code,
                    'type' => 'phone',
                    'contact_id' => $contact_id,
                    'lang' => $language,
                ];

                $id = $db->QueryInsert('lk_notice', $params);

                if ($id) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @param $id
     * @return ClientAccount|bool
     */
    public static function validateClient($id)
    {
        if (is_array($id) || !$id || !preg_match("/^\d{1,6}$/", $id)) {
            return false;
        }

        $c = self::getAccount($id);

        if (!$c) {
            return false;
        }

        return $c;
    }

    public static function validateContact($clientId, $id)
    {
        if (is_array($id) || !$id || !preg_match("/^\d{1,6}$/", $id)) {
            return false;
        }

        $contact = ClientContact::findOne([
            'client_id' => $clientId,
            'id' => $id
        ]);

        if (!$contact) {
            return false;
        }

        return $contact;
    }

    private static function _getPaymentTypeName($pay)
    {
        if (isset(PaymentModel::$types[$pay["type"]])) {
            $v = PaymentModel::$types[$pay["type"]];
            if (isset(PaymentModel::$banks[$pay["type"]])) {
                $v = PaymentModel::$banks[$pay["type"]];
            } elseif (isset(PaymentModel::$ecash[$pay["type"]])) {
                $v = PaymentModel::$ecash[$pay["type"]];
            }
        } else {
            $v = PaymentModel::TYPE_BANK;
        }
        return $v;
    }

    public static function _exportModelRow($fields, &$row)
    {
        $spec_chars = ['/\t/u', '/\f/u', '/\n/u', '/\r/u', '/\v/u'];
        $line = [];
        foreach ($fields as $field) {
            $line[$field] = preg_replace($spec_chars, ' ', $row->{$field});
        }
        return $line;
    }

    private static function _importModelRow($fields)
    {
        foreach ($fields as $k => $v) {
            $fields[$k] = $v;
        }
        return $fields;
    }

    public static function _getInternetTarifs($type = 'I', $currency = 'RUB', $status = 'public')
    {
        $ret = [];
        foreach (NewBill::find_by_sql("
            SELECT
                *
            FROM
                `tarifs_internet`
            WHERE
                `type` = ?
            AND `currency` = ?
            AND `status` = ?
            ORDER BY
                `id`
            ", [$type, $currency, $status]) as $service) {
            $line = self::_exportModelRow(["id", "name", "pay_once", "pay_month", "mb_month", "pay_mb", "comment", "type_internet", "sum_deposit", "type_count", "month_r", "month_r2", "month_f", "pay_r", "pay_r2", "pay_f", "adsl_speed"],
                $service);
            $ret[] = $line;
        }
        return $ret;
    }

    /**
     * Создание заявки (trouble ticket)
     *
     * @param string $message
     * @param string $client
     * @param User $user
     * @param string $service
     * @param int $service_id
     * @return int
     */
    public static function createTT($message = '', $client = '', User $user = null, $service = '', $service_id = 0)
    {
        $R = [
            'trouble_type' => Trouble::TYPE_CONNECT,
            'trouble_subtype' => Trouble::SUBTYPE_CONNECT,
            'client' => $client,
            'time' => '',
            'date_start' => date('Y-m-d H:i:s'),
            'date_finish_desired' => date('Y-m-d H:i:s'),
            'problem' => $message,
            'is_important' => '0',
            'bill_no' => null,
            'service' => $service,
            'service_id' => $service_id,
            'user_author' => "AutoLK"
        ];

        \Yii::info("[lk] Заказ услуги: " . $message);
        \Yii::info("[lk-trouble]: " . var_export($R, true));

        if ($user && $user->email) {
            mail($user->email, "[lk] Заказ услуги", $message);
        }

        include_once PATH_TO_ROOT . "modules/tt/module.php";
        $tt = new m_tt();

        $troubleId = $tt->createTrouble($R, $user->user);

        return $troubleId;
    }

    /**
     * Получение юзера для создания заявки
     *
     * @param string $manager
     * @return User
     */
    private static function _getUserForTrouble($manager)
    {
        $userUser = null;

        if (defined("API__USER_FOR_TROUBLE")) {
            $userUser = API__USER_FOR_TROUBLE;
        }

        if (!$userUser && $manager) {
            $userUser = $manager;
        }

        if (!$userUser) {
            $userUser = self::DEFAULT_MANAGER;
        }

        if ($user = User::findOne(['user' => $userUser])) {
            return $user;
        }

        return User::findOne(['user' => self::DEFAULT_MANAGER]);
    }

    private static function _getUserLK()
    {
        global $db;
        $default_user = 48;

        $user = $db->GetValue('SELECT id FROM user_users WHERE user="AutoLK" LIMIT 1');
        if ($user > 0) {
            return $user;
        } else {
            return $default_user;
        }
    }

    public static function checkVoipNumber($number)
    {
        if (strpos($number, '7800') === 0) {
            $check = UsageVoip::find()->actual()->phone($number)->one();
        } else {
            $check = Number::findOne(["number" => $number]);
        }

        return (bool)$check;
    }

    public static function getPayPalToken($accountId, $sum, $host, $lang)
    {
        if (!isset(Yii::$app->params['LK_PATH']) || !Yii::$app->params['LK_PATH']) {
            throw new Exception("format_error");
        }

        if (is_array($accountId) || !$accountId || !preg_match("/^\d{1,6}$/", $accountId)) {
            throw new Exception("account_is_bad");
        }

        $sum = (float)$sum;

        if (!$sum || $sum < 1 || $sum > 1000000) {
            throw new Exception("data_error");
        }


        $account = self::getAccount($accountId);
        if (!$account) {
            throw new Exception("account_not_found");
        }

        $lang = Language::normalizeLang($lang);

        $paypal = new PayPal();
        $paypal->setHost($host);
        return $paypal->getPaymentToken($accountId, $sum, $account->currency, $lang);
    }

    public static function paypalApply($token, $payerId)
    {
        $paypal = new PayPal();
        return ['result' => $paypal->paymentApply($token, $payerId)];
    }
}
