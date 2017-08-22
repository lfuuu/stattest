<?php

namespace app\dao;

use app\classes\Language;
use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\BillOwner;
use app\models\ClientAccount;
use app\models\LogBill;
use app\models\Organization;
use app\models\Transaction;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\Bill as uuBill;
use Yii;


/**
 * @method static BillDao me($args = null)
 */
class BillDao extends Singleton
{
    const PRICE_PRECISION = 2;

    /**
     * Получение следующего номера счета
     *
     * @param \DateTime|string $billDate
     * @param int $organizationId
     * @return string
     */
    public function spawnBillNumber($billDate, $organizationId = Organization::MCN_TELEKOM)
    {
        if (is_numeric($billDate)) {
            $timestamp = $billDate;
            $billDate = new \DateTime(null, new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));
            $billDate->setTimestamp($timestamp);
        }

        if (!$billDate && !($billDate instanceof \DateTime)) {
            $billDate = new \DateTime($billDate, new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));
        }

        if (!$billDate) {
            $billDate = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));
        }

        $billNoPrefix = $billDate->format('Ym') . '-';

        $organizationPrefix = '';
        if ($billDate >= $this->getDateAfterBillNoWithOrganization()) {
            $organizationPrefix = sprintf('%02d', $organizationId);
        }

        $lastBillNumber = Bill::find()
            ->select('bill_no')
            ->where(['LIKE', 'bill_no', $billNoPrefix . $organizationPrefix . '____', $isEscape = false])
            ->orderBy(['bill_no' => SORT_DESC])
            ->limit(1)
            ->scalar();

        $suffix = $lastBillNumber ? 1 + intval(substr($lastBillNumber, strlen($billNoPrefix) + strlen($organizationPrefix))) : 1;

        return $billNoPrefix . $organizationPrefix . sprintf("%04d", $suffix);
    }

    /**
     * Дата, после которой номер счета с организацией
     *
     * @return \DateTime
     */
    public function getDateAfterBillNoWithOrganization()
    {
        return new \DateTime('2017-06-01', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));
    }

    /**
     * Пересчет счета
     *
     * @param Bill $bill
     * @throws \Exception
     */
    public function recalcBill(Bill $bill)
    {
        $dbTransaction = Bill::getDb()->beginTransaction();
        try {
            $lines = $bill->getLines()->all();

            $this->_calculateBillSum($bill, $lines);

            if ($bill->biller_version === null || $bill->biller_version == ClientAccount::VERSION_BILLER_USAGE) {
                $this->_updateTransactions($bill, $lines);
            }

            $bill->save();

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    /**
     * Пересчет суммы счета
     *
     * @param Bill $bill
     * @param array $lines
     */
    private function _calculateBillSum(Bill $bill, array $lines)
    {
        $bill->sum_with_unapproved = 0;

        /** @var BillLine[] $lines */
        foreach ($lines as $line) {
            if ($line->type == BillLine::LINE_TYPE_ZADATOK) {
                continue;
            }

            $bill->sum_with_unapproved += $line->sum;
        }

        if ($bill->is_rollback && $bill->sum_with_unapproved > 0) {
            $bill->sum_with_unapproved = -$bill->sum_with_unapproved;
        }

        $bill->sum = $bill->is_approved ? $bill->sum_with_unapproved : 0;
    }

    /**
     * Обновить транзакции счета
     *
     * @param Bill $bill
     * @param array $lines
     */
    private function _updateTransactions(Bill $bill, array $lines)
    {
        $transactions = Transaction::find()
            ->andWhere([
                'bill_id' => $bill->id
            ])
            ->indexBy('bill_line_id')
            ->all();

        if ($bill->is_approved) {
            /** @var BillLine[] $lines */
            foreach ($lines as $line) {
                if ($line->type == BillLine::LINE_TYPE_ZADATOK) {
                    continue;
                }

                if (isset($transactions[$line->pk])) {
                    Transaction::dao()->updateByBillLine($bill, $line, $transactions[$line->pk]);
                    unset($transactions[$line->pk]);
                } else {
                    Transaction::dao()->insertByBillLine($bill, $line);
                }
            }
        }

        foreach ($transactions as $transaction) {
            if ($transaction->source == Transaction::SOURCE_STAT) {
                Transaction::dao()->markDeleted($transaction);
            } else {
                $transaction->delete();
            }
        }
    }

    /**
     * Получение типа документа по номеру
     *
     * @param string $bill_no
     * @return array
     */
    public function getDocumentType($bill_no)
    {
        if (preg_match("/\d{2}-\d{8}/", $bill_no)) {
            return ['type' => Bill::DOC_TYPE_INCOMEGOOD];
        }

        if (preg_match("/20\d{4}\/(\d{2})?\d{4}/", $bill_no)) {
            return ['type' => Bill::DOC_TYPE_BILL, 'bill_type' => Bill::TYPE_1C];
        }

        if (preg_match("/20\d{4}-(\d{2})?\d{4}/", $bill_no) || preg_match("/[4567]\d{5}/", $bill_no)) { // mcn telekom || all4net
            return ['type' => Bill::DOC_TYPE_BILL, 'bill_type' => Bill::TYPE_STAT];
        }

        return ['type' => Bill::DOC_TYPE_UNKNOWN];
    }

    /**
     * Закрыт ли счет
     *
     * @param Bill $bill
     * @return bool
     * @throws \yii\db\Exception
     */
    public function isClosed(Bill $bill)
    {
        $stateId = Yii::$app->db->createCommand('
                    SELECT state_id
                    FROM tt_troubles t, tt_stages s
                    WHERE bill_no = :billNo and  t.cur_stage_id = s.stage_id
                ', [':billNo' => $bill->bill_no]
        )->queryScalar();

        return $stateId == 20;
    }

    /**
     * Получить менеджера счета
     *
     * @param string $billNo
     * @return bool|string
     */
    public function getManager($billNo)
    {
        return BillOwner::find()
            ->select('owner_id')
            ->andWhere(['bill_no' => $billNo])
            ->scalar();
    }

    /**
     * Установить менеджер счета
     *
     * @param string $billNo
     * @param int $userId
     */
    public function setManager($billNo, $userId)
    {
        $owner = BillOwner::findOne($billNo);
        if ($userId) {
            if ($owner == null) {
                $owner = new BillOwner();
            }

            $owner->bill_no = $billNo;
            $owner->owner_id = $userId;
            $owner->save();
        } elseif ($owner) {
            // $owner->delete();
        }
    }

    /**
     * Функция переноса проводок универсального биллера в "старые" счета
     *
     * @param uuBill $uuBill
     * @return bool
     * @throws \Exception
     */
    public function transferUniversalBillsToBills(uuBill $uuBill)
    {
        /** @var Bill $bill */
        $bill = Bill::find()
            ->where(['uu_bill_id' => $uuBill->id])
            ->one();

        if (!$uuBill->price) {
            // нулевые счета не нужны
            if ($bill && !$bill->delete()) {
                throw new ModelValidationException($bill);
            }

            return false;
        }

        $clientAccount = $uuBill->clientAccount;

        if (!$bill) {
            $uuBillDateTime = new \DateTimeImmutable($uuBill->date);

            $bill = new Bill();
            $bill->client_id = $clientAccount->id;
            $bill->currency = $clientAccount->currency;
            $bill->nal = $clientAccount->nal;
            $bill->is_show_in_lk = 0;
            $bill->is_user_prepay = 0;
            $bill->is_approved = 1;
            $bill->bill_date = $uuBillDateTime->format(DateTimeZoneHelper::DATE_FORMAT);
            $bill->sum_with_unapproved = $uuBill->price;
            $bill->price_include_vat = $clientAccount->price_include_vat;
            $bill->sum = $uuBill->price;
            $bill->bill_no = $this->spawnBillNumber($uuBillDateTime);
            $bill->biller_version = ClientAccount::VERSION_BILLER_UNIVERSAL;
            $bill->uu_bill_id = $uuBill->id;
            if (!$bill->save()) {
                throw new ModelValidationException($bill);
            }
        }

        $toRecalculateBillSum = false;
        $billLinePosition = 0;

        // новые проводки
        /** @var AccountEntry[] $accountEntries */
        $accountEntries = $uuBill
            ->getAccountEntries()
            ->andWhere(['>', 'price', 0])// пустые строки нужны для расчета партнерского вознаграждения
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if (!$clientAccount->is_postpaid) {
            /*
                // для предоплаты не надо включать в счет посуточную абонентку
                // или все-таки надо? иначе эти строчки никогда не попадут в счет и бухгалтерский баланс будет сильно отличаться от реалтайма
                $accountEntries = array_filter($accountEntries, function (AccountEntry $accountEntry) {
                    return
                        $accountEntry->type_id != AccountEntry::TYPE_ID_PERIOD ||
                        $accountEntry->tariffPeriod->charge_period_id != Period::ID_DAY;
                });
            */

            // если не осталось строчек счета, то и сам счет не нужен
            if (!count($accountEntries) && !$bill->isNewRecord && !$bill->delete()) {
                throw new ModelValidationException($bill);
            }

            // могла измениться сумма счета - обновить ее
            $billPrice = array_reduce(
                $accountEntries,
                function ($carry, AccountEntry $accountEntry) {
                    $carry += $accountEntry->price_with_vat;
                    return $carry;
                },
                0
            );
            $billPrice = round($billPrice, self::PRICE_PRECISION);
            if ($billPrice != $bill->sum) {
                $bill->sum = $bill->sum_with_unapproved = $billPrice;
                if (!$bill->save()) {
                    throw new ModelValidationException($bill);
                }
            }
        }

        // старые проводки
        $lines = $bill->getLines()
            ->all();

        /** @var BillLine $line */
        foreach ($lines as $line) {

            $accountEntryId = $line->uu_account_entry_id;
            if (!isset($accountEntries[$accountEntryId])) {
                // была, но сейчас нет. Удалить
                if (!$line->delete()) {
                    throw new ModelValidationException($line);
                }

                continue;
            }

            // была и осталась
            $accountEntry = $accountEntries[$accountEntryId];
            $sum = round($accountEntry->price_with_vat, self::PRICE_PRECISION);
            $sumWithoutTax = round($accountEntry->price_without_vat, self::PRICE_PRECISION);
            if (
                (float)$line->sum != $sum
                || (float)$line->sum_without_tax != $sumWithoutTax
                || (float)$line->amount != (float)$accountEntry->getAmount()
                || $line->item != $accountEntry->fullName
            ) {
                // ... но изменилась. Обновить
                $line->sum = $sum;
                $line->sum_without_tax = $sumWithoutTax;
                $line->amount = $accountEntry->getAmount();
                if ($line->amount > 0 && $line->amount != 1) {
                    $line->price = $accountEntry->price_with_vat / $line->amount; // цена за "1 шт."
                    $line->price = round($line->price, self::PRICE_PRECISION);
                    if ($line->price) {
                        $line->amount = $line->sum / $line->price; // после округления price и sum надо подкорректировать коэффициент
                    }
                } else {
                    $line->amount = 1;
                    $line->price = $sum;
                }

                $line->item = $accountEntry->fullName;
                if (!$line->save()) {
                    throw new ModelValidationException($line);
                }

                $toRecalculateBillSum = true;
            }

            unset($accountEntries[$accountEntryId]);
            $billLinePosition = max($billLinePosition, $line->sort);
        }

        unset($lines, $line);

        // не было, но стало. Добавить
        foreach ($accountEntries as $accountEntry) {

            $billLinePosition++;
            $sum = round($accountEntry->price_with_vat, self::PRICE_PRECISION);
            $sumWithoutTax = round($accountEntry->price_without_vat, self::PRICE_PRECISION);

            $line = new BillLine();
            $line->sort = $billLinePosition;
            $line->bill_no = $bill->bill_no;

            $line->item = $accountEntry->fullName;
            $line->date_from = $accountEntry->date_from;
            $line->date_to = $accountEntry->date_to;
            $line->type = BillLine::LINE_TYPE_SERVICE;
            $line->amount = $accountEntry->getAmount();
            $line->tax_rate = $accountEntry->vat_rate;
            $line->sum = $sum;
            $line->sum_without_tax = $sumWithoutTax;
            $line->sum_tax = $accountEntry->vat;
            $line->uu_account_entry_id = $accountEntry->id;
            $line->service = 'uu_account_tariff';
            $line->id_service = $accountEntry->account_tariff_id;
            $line->item_id = null;
            if ($line->amount > 0 && $line->amount != 1) {
                $line->price = $accountEntry->price_with_vat / $line->amount; // цена за "1 шт."
                $line->price = round($line->price, self::PRICE_PRECISION);
                if ($line->price) {
                    $line->amount = $line->sum / $line->price; // после округления price надо подкорректировать коэффициент
                }
            } else {
                $line->amount = 1;
                $line->price = $sum;
            }

            if (!$line->save()) {
                throw new ModelValidationException($line);
            }

            $toRecalculateBillSum = true;
        }

        if ($toRecalculateBillSum) {
            Bill::dao()->recalcBill($bill);
        }

        $uuBill->is_converted = 1;
        if (!$uuBill->save()) {
            throw new ModelValidationException($uuBill);
        }
    }

    /**
     * Этот счет выписывается на новую компанию?
     *
     * Переход с МСН Телкома на МСН Телеком Ритейл
     *
     * @param Bill $bill
     * @return bool
     * @throws \yii\db\Exception
     */
    public function isBillNewCompany(Bill $bill)
    {
        $sql = <<<ESQL
            SELECT
                model,
                model_id,
                date,
                replace(replace(SUBSTRING(data_json, instr(data_json, 'organization_id') + 15,
                                        instr(SUBSTRING(data_json, instr(data_json, 'organization_id') + 15), ',') - 1), '\"', ''),
                      ':', '')              new_org_id,
                replace(replace(SUBSTRING(h2_json, instr(h2_json, 'organization_id') + 15,
                                        instr(SUBSTRING(h2_json, instr(h2_json, 'organization_id') + 15), ',') - 1), '\"',
                              ''), ':', '') old_org_id,
                client_id
            FROM (
                   SELECT
                     h1.*,
                     c.id AS   client_id,
                     (SELECT data_json
                      FROM history_version h2
                      WHERE h2.model = 'app\\\\models\\\\ClientContract' AND h2.date < :billDate AND h1.model_id = h2.model_id
                      ORDER BY date DESC
                      LIMIT 1) h2_json
                   FROM history_version h1, clients c
                   WHERE h1.model = 'app\\\\models\\\\ClientContract' AND h1.date = :billDate AND c.id = :accountId AND h1.model_id = c.contract_id
                 ) a
            HAVING new_org_id = 11 AND old_org_id = 1
ESQL;

        return (bool)\Yii::$app->db->createCommand($sql, [
            ':billDate' => $bill->bill_date,
            ':accountId' => $bill->client_id
        ])->queryOne();
    }

    /**
     * Дата перехода с МСН Телкома на МСН Телеком Ритейл
     *
     * @param integer $accountId
     * @return false|null|string
     */
    public function getNewCompanyDate($accountId)
    {
        $sql = <<<SQL
            select 
                date 
            from (
                SELECT
                    model,
                    model_id,
                    date,
                    replace(replace(SUBSTRING(data_json, instr(data_json, 'organization_id') + 15,
                                instr(SUBSTRING(data_json, instr(data_json, 'organization_id') + 15), ',') - 1), '\"',
                            ''), ':', '') new_org_id,
                    replace(replace(SUBSTRING(h2_json, instr(h2_json, 'organization_id') + 15,
                                instr(SUBSTRING(h2_json, instr(h2_json, 'organization_id') + 15), ',') - 1), '\"',
                            ''), ':', '') old_org_id,
                     client_id
                FROM (
                    SELECT
                    h1.*,c.id as client_id,
                    (SELECT data_json
                     FROM history_version h2
                     WHERE h2.model = 'app\\\\models\\\\ClientContract' AND h2.date < h1.date AND h1.model_id = h2.model_id
                     ORDER BY date DESC
                     LIMIT 1) h2_json
                    FROM history_version h1, clients c
                    WHERE h1.model = 'app\\\\models\\\\ClientContract' and c.id = :accountId and h1.model_id = c.contract_id
                    ) a
                                            
                HAVING new_org_id = 11 AND old_org_id = 1
                ORDER BY date DESC
                LIMIT 1
            ) a
SQL;

        return \Yii::$app->db->createCommand($sql, [':accountId' => $accountId])->queryScalar();
    }

    /**
     * Проверяет, у всех ли счетов, выпущенных в заданном периоде, проставлена организация
     * Эта функция необходима, до полного перехода на yii'шную модель
     *
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     */
    public function checkSetBillsOrganization(\DateTime $dateFrom, \DateTime $dateTo)
    {
        $query = Bill::find()
            ->where([
                'between',
                'bill_date',
                $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT),
                $dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
            ])
            ->andWhere(['organization_id' => 0]);

        /** @var Bill $bill */
        foreach ($query->each() as $bill) {
            $bill->organization_id = $bill->clientAccount->contract->organization_id;
            $bill->save();
        }
    }

    /**
     * Получение счета на предоплату для ЛК
     *
     * @param int $accountId
     * @param float $sum
     * @param bool $isForceCreate
     * @return Bill
     */
    public function getPrepayedBillOnSum($accountId, $sum, $isForceCreate = false)
    {
        if (!$isForceCreate) {
            $billNo = $this->getPrepayedBillNoOnSumFromDB($accountId, $sum);

            if ($billNo) {
                return Bill::findOne(['bill_no' => $billNo]);
            }
        }

        return $this->createBillOnSum($accountId, $sum);
    }

    /**
     * Получение счета на предоплату для Лк из базы
     *
     * @param int $accountId
     * @param float $sum
     * @return null|Bill
     */
    public function getPrepayedBillNoOnSumFromDB($accountId, $sum)
    {
        return \Yii::$app->db->createCommand(
            "SELECT
                bill_no
             FROM (
                SELECT
                    b.bill_no,
                    p.payment_no
                FROM (
                        SELECT
                            b.bill_no,
                            b.client_id,
                            bill_date,
                            COUNT(1) AS count_lines,
                            SUM(l.sum) AS l_sum
                        FROM
                            newbills b, newbill_lines l
                        WHERE
                                b.client_id = :accountId
                            AND l.bill_no = b.bill_no
                            AND is_user_prepay
                            AND biller_version = :biller_version
                        GROUP BY
                            bill_no
                        HAVING
                                count_lines = 1
                            AND l_sum = :sum
                ) b
                LEFT JOIN newpayments p ON (p.client_id = b.client_id and (b.bill_no = p.bill_no OR b.bill_no = p.bill_vis_no))
                HAVING
                    p.payment_no IS NULL
                ORDER BY
                    bill_date DESC
                LIMIT 1
             )a", [
            ':biller_version' => ClientAccount::VERSION_BILLER_USAGE,
            ':accountId' => $accountId,
            'sum' => $sum
        ])->queryScalar();

    }

    /**
     * Создает счет на основе суммы платежа
     *
     * @param int $accountId
     * @param float $sum
     * @return Bill
     * @throws \Exception
     * @internal param bool|false $createAutoLkLog
     */
    public function createBillOnSum($accountId, $sum)
    {
        $clientAccount = ClientAccount::findOne(['id' => $accountId]);

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $bill = self::me()->createBill($clientAccount);

            $bill->is_user_prepay = 1;
            $bill->save();

            $bill->addLine(
                Yii::t('biller', 'incomming_payment', [], Language::normalizeLang($clientAccount->country->lang)),
                1,
                $sum,
                BillLine::LINE_TYPE_ZADATOK
            );

            LogBill::dao()->log($bill->bill_no, 'Создание счета');

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $bill;
    }

    /**
     * Создание пустого счета
     *
     * @param ClientAccount $clientAccount
     * @param \DateTime|null $date
     * @return Bill
     */
    public function createBill(ClientAccount $clientAccount, \DateTime $date = null)
    {
        if (!$date) {
            $date = new \DateTime('now', new \DateTimeZone($clientAccount->timezone_name));
        }

        $bill = new Bill();
        $bill->client_id = $clientAccount->id;
        $bill->currency = $clientAccount->currency;
        $bill->bill_no = self::me()->spawnBillNumber($date, $clientAccount->contract->organization_id);
        $bill->bill_date = $date->format(DateTimeZoneHelper::DATE_FORMAT);
        $bill->nal = $clientAccount->nal;
        $bill->is_approved = 1;
        $bill->price_include_vat = $clientAccount->price_include_vat;
        $bill->biller_version = $clientAccount->account_version;
        $bill->save();

        $bill->refresh();

        return $bill;
    }
}
