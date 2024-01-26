<?php

namespace app\dao;

use app\controllers\api\internal\SimController;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContract;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\classes\Smarty;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\ClientDocument;
use app\models\document\DocumentFolder;
use app\models\document\DocumentTemplate;
use app\models\TariffVoip;


/**
 * @method static ClientDocumentDao me($args = null)
 */
class ClientDocumentDao extends Singleton
{

    /**
     * Получение раздела по умолчанию от бизнес-процесса
     *
     * @param int $businessId
     * @return DocumentFolder
     */
    public static function getFolderIsDefaultForBusiness($businessId)
    {
        return DocumentFolder::findOne([
            'default_for_business_id' => $businessId,
            'parent_id' => 0,
        ]);
    }

    /**
     * Получение списка разделов верхнего уровня с указанным типом документов
     *
     * @param string $documentType
     * @return array
     */
    public static function getFolders($documentType = ClientDocument::DOCUMENT_CONTRACT_TYPE)
    {
        $query = self::_getFoldersWithDocumentsData()
            ->andWhere(['folders.parent_id' => 0])
            ->andWhere(['documents.type' => $documentType]);

        return
            ArrayHelper::map(
                $query->all(),
                'id',
                'name'
            );
    }

    /**
     * Получение списка вложенных разделов с документами с типом отличным от "Контракт"
     *
     * @param string[] $documentTypes
     * @return array
     */
    public static function getFoldersByDocumentType($documentTypes = [])
    {
        $query = self::_getFoldersWithDocumentsData()
            ->andWhere(['IN', 'documents.type', $documentTypes]);

        $result = [];

        foreach ($query->all() as $row) {
            $result[$row['parent_id']][] = [
                'id' => $row['id'],
                'name' => $row['name'],
            ];
        }

        return $result;
    }

    /**
     * Получение списка документов, находящихся в разделах
     *
     * @return array
     */
    public static function getTemplates()
    {
        $templates = DocumentTemplate::find()
            ->joinWith(['folder'])
            ->orderBy([
                'document_template.sort' => SORT_DESC,
                'document_template.name' => SORT_ASC,
            ])
            ->asArray()
            ->all();

        $res = [];

        foreach ($templates as $template) {
            $res[] = [
                'id' => $template['id'],
                'name' => $template['name'],
                'type' => $template['type'],
                'folder_id' => $template['folder_id'],
            ];
        }

        return $res;
    }

    /**
     * @param ClientDocument $document
     * @return bool
     */
    public function deleteFile(ClientDocument $document)
    {
        $file = $this->_getFilePath($document);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * @param ClientDocument $document
     * @param int $contractTemplateId
     * @return int
     */
    public function generateFile(ClientDocument $document, $contractTemplateId)
    {
        $content = DocumentTemplate::findOne($contractTemplateId)['content'];
        file_put_contents($this->_getFilePath($document), $content);
        return $this->_generateDefault($document);
    }

    /**
     * @param ClientDocument $document
     * @return int
     */
    public function updateFile(ClientDocument $document)
    {
        return file_put_contents($this->_getFilePath($document), $document->content);
    }

    /**
     * @param ClientDocument $document
     * @return string
     */
    public function getFileContent(ClientDocument $document)
    {
        $file = $this->_getFilePath($document);

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return '';
    }

    /**
     * @param ClientDocument $document
     * @return int
     */
    private function _generateDefault(ClientDocument $document)
    {
        $file = $this->_getFilePath($document);
        if (!file_exists($file)) {
            return 0;
        }

        $content = file_get_contents($file);

        $design = \app\classes\Smarty::init();
        $design->assign($this->_spawnDocumentData($document));

        $content = $this->_contractFixStaticPartsOfTemplate($design, $content);
        if (strpos($content, '{*#blank_zakaz#*}') !== false) {
            $content = str_replace('{*#blank_zakaz#*}', $this->_makeBlankZakaz($document, $design), $content);
        }

        if (strpos($content, '{*#simcards_table#*}') !== false) {
            $cc = $this->_makeSimCardsTable($document, $design);
            $content = str_replace('{*#simcards_table#*}', $cc, $content);
        }

        file_put_contents($file, $content);
        $newDocument = $design->fetch($file);
        return file_put_contents($file, $newDocument);
    }

    /**
     * @param ClientDocument $document
     * @return string
     */
    private function _getFilePath(ClientDocument $document)
    {
        $contractId = $document->account_id ? $document->account_id : $document->contract_id;
        return Yii::$app->params['STORE_PATH'] . 'contracts/' . $contractId . '-' . $document->id . '.html';
    }

    /**
     * @param string $content
     */
    private function _fixStyle(&$content)
    {
        if (strpos($content, '{/literal}</style>') === false) {
            $content = preg_replace(
                '/<style([^>]*)>(.*?)<\/style>/six',
                '<style\\1>{literal}\\2{/literal}</style>',
                $content
            );
        }
    }

    /**
     * Строки в таблицу списка сим карт
     *
     * @param ClientDocument $document
     * @param $design
     * @return mixed
     */
    private function _makeSimCardsTable(ClientDocument $document, &$design)
    {
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $clientAccountIds = ClientAccount::find()
            ->select('id')
            ->where(['contract_id' => $document->contract_id])
            ->column();
        $numbers = Number::find()
            ->select('number')
            ->joinWith('accountTariff')
            ->leftJoin($accountTariffLogTableName, $accountTariffTableName.'.id=' . $accountTariffLogTableName . '.account_tariff_id')
            ->where(['client_id' => $clientAccountIds])
            ->andWhere(['ndc_type_id' => NdcType::ID_MOBILE])
            ->andWhere(['OR',
                ['NOT', [$accountTariffTableName . '.tariff_period_id' => null]],
                ['AND',
                    ['NOT', [$accountTariffLogTableName . '.tariff_period_id' => null]],
                    ['>', $accountTariffLogTableName . '.actual_from_utc', new Expression('UTC_TIMESTAMP()')]
                ]
            ])
            ->groupBy('number')
            ->column();
        // Получение карт и перепаковка данных под нужную структуру
        $cards = array_map(function($item) {
            if (isset($item['imsies'])) {
                foreach ($item['imsies'] as $key => $value) {
                    // Только для партнера MTT
                    if ($value['partner_id'] === Imsi::PARTNER_MTT) {
                        $item['msisdn'] = $value['msisdn'];
                        break;
                    }
                }
            } else {
                $item['msisdn'] = '';
            }
            return $item;
        }, Card::find()
            ->alias('c')
            ->select(['c.iccid'])
            ->joinWith('imsies')
            ->where([
                'c.client_account_id' => $clientAccountIds
            ])
            ->asArray()
            ->all()
        );
        $numbers = array_diff($numbers, array_column($cards, 'msisdn'));

        $design->assign('sims', $cards);
        $design->assign('numbers', $numbers);

        return $design->fetch('tarifs/simcards.tpl');
    }


    /**
     * @param ClientDocument $document
     * @param \Smarty $design
     * @return mixed
     */
    private function _makeBlankZakaz(ClientDocument $document, &$design)
    {
        /** @var ClientAccount $clientAccount */
        $clientAccount = $document->clientAccount;
        $client = $clientAccount->client;

        $data = [
            'voip' => [],
            'uu' => [],
            'ip' => [],
            'colocation' => [],
            'vpn' => [],
            'welltime' => [],
            'vats' => [],
            'sms' => [],
            'extra' => []
        ];
        $data['has8800'] = false;

        $taxRate = $clientAccount->getTaxRate();

        foreach (\app\models\UsageVoip::find()->client($client)->andWhere('actual_to > NOW()')->all() as $usage) {

            $usageTaxRate = ($usage->tariff->price_include_vat && $taxRate) ? false : $taxRate;
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->getAbonPerMonth(), $usageTaxRate);

            $currentTariff = $usage->getLogTariff();

            $row = [
                'from' => strtotime($usage->actual_from),
                'address' => $usage->datacenter ? $usage->datacenter->address : '',
                'description' => 'Телефонный номер: ' . $usage->E164,
                'number' => $usage->E164 . ' x ' . $usage->no_of_lines,
                'lines' => $usage->no_of_lines,
                'free_local_min' => $usage->tariff->free_local_min * ($usage->tariff->freemin_for_number ? 1 : $usage->no_of_lines),
                'connect_price' => (string)$usage->voipNumber->price,
                'tariff' => $usage->tariff,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2),
            ];

            if (!$data['has8800'] && in_array($usage->tariff->id, [226, 263, 264, 321, 322, 323, 448])) {
                $data['has8800'] = true;
            }

            if (
                $currentTariff->id_tarif_local_mob
                && ($tarifLocalMob = TariffVoip::findOne($currentTariff->id_tarif_local_mob)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifLocalMob */
                $row['voip_current_tariff']['tarif_local_mob'] = $tarifLocalMob->name;
            }

            if (
                $currentTariff->id_tarif_russia_mob
                && ($tarifRussiaMob = TariffVoip::findOne($currentTariff->id_tarif_russia_mob)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifRussiaMob */
                $row['voip_current_tariff']['tarif_russia_mob'] = $tarifRussiaMob->name;
            }

            if (
                $currentTariff->id_tarif_russia
                && ($tarifRussia = TariffVoip::findOne($currentTariff->id_tarif_russia)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifRussia */
                $row['voip_current_tariff']['tarif_russia'] = $tarifRussia->name;
            }

            if (
                $currentTariff->id_tarif_intern
                && ($tarifIntern = TariffVoip::findOne($currentTariff->id_tarif_intern)) instanceof TariffVoip
            ) {
                /** @var TariffVoip $tarifIntern */
                $row['voip_current_tariff']['tarif_intern'] = $tarifIntern->name;
            }

            if ($currentTariff->dest_group != 0 && $currentTariff->minpayment_group) {
                $group = preg_split('//', $currentTariff->dest_group, -1, PREG_SPLIT_NO_EMPTY);
                $minpayment = ['value' => $currentTariff->minpayment_group, 'variants' => [0, 0, 0, 0,]];
                for ($i = 0, $s = count($group); $i < $s; $i++) {
                    switch ($group[$i]) {
                        case 1:
                            $minpayment['variants'][1] = 1;
                            $minpayment['variants'][2] = 1;
                            break;
                        case 2:
                            $minpayment['variants'][3] = 1;
                            break;
                        case 5:
                            $minpayment['variants'][0] = 1;
                            break;
                    }
                }

                $row['minpayments'][] = $minpayment;
            }

            if ($currentTariff->minpayment_local_mob > 0) {
                $row['minpayments'][] = ['value' => $currentTariff->minpayment_local_mob, 'variants' => [1, 0, 0, 0,]];
            }

            if ($currentTariff->minpayment_russia > 0) {
                $row['minpayments'][] = ['value' => $currentTariff->minpayment_russia, 'variants' => [0, 1, 1, 0,]];
            }

            if ($currentTariff->minpayment_intern > 0) {
                $row['minpayments'][] = ['value' => $currentTariff->minpayment_intern, 'variants' => [0, 0, 0, 1,]];
            }

            if ($usage->ndc_type_id == NdcType::ID_FREEPHONE) {
                $data['voip_7800'][] = $row;
            } else {
                $data['voip'][] = $row;
            }
        }

        /** @var AccountTariff $accountTariff */
        foreach (AccountTariff::find()
                     ->where([
                         'client_account_id' => $clientAccount->id,
                     ])
                     ->andWhere(['NOT', ['tariff_period_id' => null]])
                     ->orderBy([
                         'service_type_id' => SORT_ASC,
                         'id' => SORT_ASC
                     ])
                     ->each() as $accountTariff
        ) {

            if (!($logs = $accountTariff->accountTariffLogs)) {
                continue;
            }

            $log = reset($logs);

            if (!$log->tariff_period_id) {
                continue;
            }

            $period = $log->tariffPeriod;
            $tariff = $period->tariff;

            if (!$tariff) {
                throw new \LogicException('Тариф не найден!');
            }

            $row = [
                'from' => (new \DateTimeImmutable($log->actual_from_utc,
                    new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
                    ->setTimezone(new \DateTimeZone($clientAccount->timezone_name))
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
                'number' => $accountTariff->voip_number,
                'period_charge' => $period->chargePeriod->name,
                'price_per_period' => Yii::$app->formatter->asCurrency($period->price_per_period, $tariff->currency_id),
                'price_per_setup' => Yii::$app->formatter->asCurrency($period->price_setup, $tariff->currency_id),
                'price_per_min' => Yii::$app->formatter->asCurrency($period->price_min, $tariff->currency_id),
                'tariff_name' => $tariff->name,
            ];

            if (!isset($data['uu'][$accountTariff->service_type_id])) {
                $data['uu'][$accountTariff->service_type_id] = [
                    'is_voip' => $accountTariff->service_type_id == ServiceType::ID_VOIP,
                    'name' => $accountTariff->serviceType->name,
                    'rows' => [],
                ];
            }

            $data['uu'][$accountTariff->service_type_id]['rows'][] = $row;
        }

        foreach (\app\models\UsageIpPorts::find()->client($client)->actual()->all() as $usage) {

            $usageTaxRate = ($usage->tariff->price_include_vat && $taxRate) ? false : $taxRate;
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->tariff->pay_month, $usageTaxRate);

            switch ($usage->tariff->type) {
                case 'C':
                    $block = 'colocation';
                    break;
                case 'V':
                    $block = 'vpn';
                    break;
                case 'I':
                default:
                    $block = 'ip';
            }

            $data[$block][] = [
                'from' => strtotime($usage->actual_from),
                'id' => $usage->id,
                'tariff' => $usage->tariff,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        foreach (\app\models\UsageVirtpbx::find()->client($client)->andWhere('actual_to > NOW()')->all() as $usage) {

            $usageTaxRate = ($usage->tariff->price_include_vat && $taxRate) ? false : $taxRate;
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->tariff->price, $usageTaxRate);

            $data['vats'][] = [
                'from' => strtotime($usage->actual_from),
                'description' => 'ВАТС ' . $usage->id,
                'tariff' => $usage->tariff,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        /*
        foreach (\app\models\UsageSms::find()->client($client)->actual()->all() as $usage) {
            $usageTaxRate = ($usage->tariff->price_include_vat && $taxRate) ? false : $taxRate;
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->tariff->per_month_price, $usageTaxRate);

            $data['sms'][] = [
                'from' => $usage->actual_from,
                'description' => "SMS-рассылка",
                'tarif_name' => $usage->tariff->description,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }
        */

        foreach (\app\models\UsageExtra::find()->client($client)->actual()->all() as $usage) {

            $usageTaxRate = ($usage->tariff->price_include_vat && $taxRate) ? false : $taxRate;
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->tariff->price * $usage->amount,
                $usageTaxRate);

            $data['extra'][] = [
                'from' => strtotime($usage->actual_from),
                'tariff' => $usage->tariff,
                'amount' => $usage->amount,
                'pay_once' => 0,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        foreach (\app\models\UsageWelltime::find()->client($client)->actual()->all() as $usage) {
            $usageTaxRate = ($usage->tariff->price_include_vat && $taxRate) ? false : $taxRate;
            list($sum, $sum_without_tax) = $clientAccount->convertSum($usage->tariff->price * $usage->amount,
                $usageTaxRate);

            $data['welltime'][] = [
                'from' => $usage->actual_from,
                'tariff' => $usage->tariff,
                'amount' => $usage->amount,
                'pay_once' => 0,
                'per_month' => round($sum, 2),
                'per_month_without_tax' => round($sum_without_tax, 2)
            ];
        }

        foreach (\app\models\UsageVoipPackage::find()->client($client)->actual()->all() as $usage) {
            $data['voip_packages'][] = [
                'usage_voip' => $usage->usageVoip,
                'tariff' => $usage->tariff,
                'from' => $usage->actual_from,
            ];
        }

        foreach (\app\models\UsageCallChat::find()->client($client)->actual()->all() as $usage) {
            $data['call_chat'][] = [
                'tariff' => $usage->tariff,
                'from' => $usage->actual_from,
            ];
        }

        $design->assign('blank_data', $data);
        return $design->fetch('tarifs/blank.htm');
    }

    /**
     * @param \Smarty $design
     * @param string $content
     * @return mixed
     */
    private function _contractFixStaticPartsOfTemplate(&$design, $content)
    {
        if (($pos = strpos($content, '{\$include_')) !== false) {
            $c = substr($content, $pos);
            $templateName = substr($c, 10, strpos($c, '}') - 10);

            $fname = Yii::$app->params['STORE_PATH'] . 'contracts/template_' . $templateName . '.html';

            if (file_exists($fname)) {
                $c = file_get_contents($fname);
                $design->assign('include_' . $templateName, $c);
            }

            $fname = Yii::$app->params['STORE_PATH'] . 'contracts/' . $templateName . '.html';
            if (file_exists($fname)) {
                $c = file_get_contents($fname);
                $design->assign('include_' . $templateName, $c);
            }
        }


        if (strpos($content, '{*#voip_moscow_tarifs_mob#*}') !== false) {
            $repl = ''; // москва(моб.)
            $content = str_replace('{*#voip_moscow_tarifs_mob#*}', $repl, $content);
        }

        $this->_fixStyle($content);

        return $content;
    }

    /**
     * @param array $f
     * @param bool $b
     * @return string
     */
    private function _generateFirmDetail($f, $b = true)
    {
        $d = $f['name'] . '<br /> Юридический адрес: ' . $f['address'] .
            (isset($f['post_address']) ? '<br /> Почтовый адрес: ' . $f['post_address'] : '') .
            '<br /> ИНН ' . $f['inn'] . ', КПП ' . $f['kpp'] .
            (
            $b ?
                '<br /> Банковские реквизиты: <br /> р/с:&nbsp;' . $f['acc'] . ' в ' . $f['bank_name'] .
                '<br /> к/с:&nbsp;' . $f['kor_acc'] .
                '<br /> БИК:&nbsp;' . $f['bik'] :
                ''
            );
        return $d;
    }

    /**
     * @param ClientAccount $account
     * @return string
     */
    private function _prepareContragentPaymentInfo(ClientAccount $account)
    {
        $contragent = $account->contract->contragent;
        $officialContacts = $account->getOfficialContact();

        $result = $contragent->name_full . '<br />Адрес: ' . $contragent->address . '<br />';

        if ($contragent->legal_type == ClientContragent::PERSON_TYPE) {
            $personData = $contragent->person->passport_serial .
                $contragent->person->passport_number .
                $contragent->person->passport_issued .
                $contragent->person->passport_date_issued;

            if ($contragent->person && !empty($personData)) {
                return
                    $result .
                    'Паспорт серия ' . $contragent->person->passport_serial .
                    ' номер ' . $contragent->person->passport_number .
                    '<br />Выдан: ' . $contragent->person->passport_issued .
                    '<br />Дата выдачи: ' . $contragent->person->passport_date_issued . ' г.' .
                    (
                    count($officialContacts) ?
                        '<br />E-mail: ' . implode('; ', $officialContacts['email']) :
                        ''
                    );
            }
        } else {
            return
                $result .
                'ИНН ' . $contragent->inn .
                ', КПП ' . $contragent->kpp .
                '<br />Банковские реквизиты: р/с ' . ($account->pay_acc ?: '') . '<br />' .
                $account->bank_name . ' ' . $account->bank_city .
                ($account->corr_acc ? '<br />к/с ' . $account->corr_acc : '') .
                ', БИК ' . $account->bik .
                (!empty($account->address_post_real) ? '<br />Почтовый адрес: ' . $account->address_post_real : '') .
                (
                count($officialContacts) ?
                    '<br />E-mail: ' . implode('; ', $officialContacts['email']) :
                    ''
                );
        }

        return '';
    }

    /**
     * @param ClientDocument $document
     * @return array
     */
    private function _spawnDocumentData(ClientDocument $document)
    {
        $account = $document->getAccount();
        $contractDate = $document->contract_date;
        $officialContacts = $account->getOfficialContact();

        if ($document->type == ClientDocument::DOCUMENT_CONTRACT_TYPE) {
            $lastContract = [
                'contract_no' => $document->contract_no,
                'contract_date' => $document->contract_date,
            ];
        } else {
            $contractDocument = ClientDocument::find()
                ->andWhere(['type' => 'contract', 'contract_id' => $account->contract_id])
                ->orderBy('is_active desc, contract_date desc, id desc')
                ->one();

            $lastContract = [
                'contract_no' => $contractDocument->contract_no,
                'contract_date' => $contractDocument->contract_date,
                'contract_dop_no' => $document->contract_no,
                'contract_dop_date' => $document->contract_date,
            ];
        }

        /** @var ClientContract $contract */
        $contract = $document->getContract();

        /** @var ClientContragent $contragent */
        $contragent = $contract->getContragent();

        $organization = $contract->getOrganization($contractDate);
        $firm = $organization->getOldModeInfo();

        $person = [];
        if ($contragent->legal_type == ClientContragent::PERSON_TYPE) {
            $person = $contragent->person;
        }

        return [
            'position' => $contragent->signer_position,
            'fio' => $contragent->signer_fio,
            'name' => $contragent->name,
            'name_full' => $contragent->name_full,

            'first_name' => $person ? $person->first_name : '',
            'last_name' => $person ? $person->last_name : '',
            'middle_name' => $person ? $person->middle_name : '',

            'birthdate' => $contragent->person ? $contragent->person->birthday : '',
            'birthplace' => $contragent->person ? $contragent->person->birthplace : '',

            'address_jur' => $contragent->address,
            'bank_properties' => nl2br($account->bank_properties),
            'bik' => $account->bik,
            'address_post_real' => $account->address_post_real,
            'address_post' => $account->address_post,
            'corr_acc' => $account->corr_acc,
            'pay_acc' => $account->pay_acc,
            'inn' => $contragent->inn,
            'kpp' => $contragent->kpp,
            'stamp' => $account->stamp,
            'legal_type' => $contragent->legal_type,
            'old_legal_type' => $contragent->legal_type != ClientContragent::PERSON_TYPE ? 'org' : 'person',
            'address_connect' => $account->address_connect,
            'account_id' => $account->id,
            'bank_name' => $account->bank_name,
            'credit' => $account->credit,

            'contract_state' => $contract->state,
            'contract_no' => $lastContract['contract_no'],
            'contract_date' => $lastContract['contract_date'],
            'contract_dop_date' => isset($lastContract['contract_dop_date']) ? $lastContract['contract_dop_date'] : null,
            'contract_dop_no' => isset($lastContract['contract_dop_no']) ? $lastContract['contract_dop_no'] : null,

            'emails' => implode('; ', $officialContacts['email']),
            'phones' => implode('; ', $officialContacts['phone']),
            'faxes' => implode('; ', $officialContacts['fax']),

            'organization_firma' => $firm['firma'],
            'organization_director_post' => $firm['director_post'], // В именительном падеже
            'organization_director' => $firm['director'], // В именительном падеже
            'organization_name' => $firm['name'],
            'organization_address' => $firm['address'],
            'organization_inn' => $firm['inn'],
            'organization_kpp' => $firm['kpp'],
            'organization_corr_acc' => $firm['kor_acc'],
            'organization_bik' => $firm['bik'],
            'organization_bank' => $firm['bank'],
            'organization_phone' => $firm['phone'],
            'organization_email' => $firm['email'],
            'organization_pay_acc' => $firm['acc'],

            'firm_detail_block' => $this->_generateFirmDetail($firm, $account->bik),
            'payment_info' => $this->_prepareContragentPaymentInfo($account),
        ];
    }

    /**
     * @return Query
     */
    private static function _getFoldersWithDocumentsData()
    {
        $query = (new Query)
            ->select([
                'folders.*',
                'documents' => new Expression('COUNT(documents.id)'),
            ])
            ->from(['folders' => DocumentFolder::tableName()])
            ->leftJoin(
                ['documents' => DocumentTemplate::tableName()],
                'documents.folder_id = folders.id'
            )
            ->groupBy('folders.id')
            ->having('documents > 0')
            ->orderBy(DocumentFolder::$orderBy);

        return $query;
    }

}
