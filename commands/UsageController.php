<?php
namespace app\commands;

use app\classes\behaviors\SetTaxVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\billing\CallsAggr;
use app\models\BusinessProcessStatus;
use app\models\ClientContract;
use app\models\ClientFlag;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\models\LogTarif;
use app\models\TariffVoip;
use Yii;
use DateTime;
use app\models\ClientAccount;
use yii\console\Controller;
use app\forms\usage\UsageVoipEditForm;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\db\Query;


class UsageController extends Controller
{

    const ACTION_SET_BLOCK = 1;
    const ACTION_SET_OFF = 2;
    const ACTION_CLEAN_TRASH = 3;

    public function actionClientUpdateIsActive()
    {
        $partSize = 500;
        try {
            $count = $partSize;
            $offset = 0;
            while ($count >= $partSize) {
                $clientAccounts =
                    ClientAccount::find()
                        ->limit($partSize)->offset($offset)
                        ->orderBy('id')
                        ->all();

                foreach ($clientAccounts as $clientAccount) {
                    $offset++;
                    ClientAccount::dao()->updateIsActive($clientAccount);
                }

                $count = count($clientAccounts);
            }

        } catch (\Exception $e) {
            Yii::error($e);
            throw $e;
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Очистка услуг телефонии.
     * через 3 дня   - высвободить номер, если статус бизнес процесса - "Заказ услуг"
     * через 10 дней - заблокировать ЛС, если есть услуга в тесте
     * через 40 дней - высвободить номер, если есть услуга в тесте
     *
     * @return int
     */
    public function actionVoipTestClean()
    {
        $info = [];

        $now = new DateTime("now");

        echo "\nstart " . $now->format(DateTimeZoneHelper::DATETIME_FORMAT) . "\n";

        $cleanOrderOfServiceDate = (new DateTime("now"))->modify("-3 day");
        $offDate = (new DateTime("now"))->modify("-10 day");

        echo $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": off:   " . $offDate->format(DateTimeZoneHelper::DATE_FORMAT) . "\n";
        echo $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": clean: " . $cleanOrderOfServiceDate->format(DateTimeZoneHelper::DATE_FORMAT) . "\n";

        $infoOff = $this->disableTestVoipUsages($offDate);
        $infoClean = $this->cleanUsages($cleanOrderOfServiceDate, self::ACTION_CLEAN_TRASH);

        if ($infoOff) {
            $info = array_merge($info, $infoOff);
        }

        if ($infoClean) {
            $info = array_merge($info, $infoClean);
        }

        if ($info) {
            if (defined("ADMIN_EMAIL") && ADMIN_EMAIL) {
                mail(ADMIN_EMAIL, "voip clean processor", implode("\n", $info));
            }

            echo implode("\n", $info);
        }

        return ExitCode::OK;
    }

    private function cleanUsages(\DateTime $date, $action)
    {
        $now = new DateTime("now");
        $yesterday = clone $now;
        $yesterday->modify('-1 day');

        $usages = UsageVoip::find()->actual()->andWhere(["actual_from" => $date->format(DateTimeZoneHelper::DATE_FORMAT)])->all();

        $info = [];

        foreach ($usages as $usage) {
            $account = $usage->clientAccount;

            if ($action == self::ACTION_CLEAN_TRASH) {
                if ($account->contract->business_process_status_id != BusinessProcessStatus::TELEKOM_MAINTENANCE_TRASH) {
                    continue;
                }

                $info[] = $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": " . $usage->E164 . ", from: " . $usage->actual_from . ": clean trash";

                $model = new UsageVoipEditForm();
                $model->initModel($account, $usage);
                $model->disconnecting_date = $yesterday->format(DateTimeZoneHelper::DATE_FORMAT);
                $model->status = UsageInterface::STATUS_WORKING;
                $model->edit();
            }
        }

        return $info;
    }

    /**
     * Отключаем услуги телефонии на тестовом тарифе
     *
     * @param DateTime $date
     * @return array
     */
    private function disableTestVoipUsages(\DateTime $date)
    {
        $now = new DateTime("now");
        $yesterday = clone $now;
        $yesterday->modify('-1 day');

        $info = [];

        $usageVoipTable = UsageVoip::tableName();
        $logTariffTable = LogTarif::tableName();
        $tariffVoipTable = TariffVoip::tableName();

        $query = \Yii::$app->getDb()->createCommand("
            SELECT
              u.id as usage_id
            FROM
              (
                SELECT
                  id AS     usage_id,
                  (SELECT id
                   FROM log_tarif
                   WHERE id_service = u.id
                         AND date_activation <= CAST(NOW() AS DATE)
                         AND service = '{$usageVoipTable}'
                   ORDER BY date_activation DESC, id DESC
                   LIMIT 1) log_tariff_id
            
                FROM {$usageVoipTable} u
                WHERE
                  CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to
              ) a, {$usageVoipTable} u, {$logTariffTable} lt, {$tariffVoipTable} tv
            WHERE u.id = usage_id
                  AND lt.id = log_tariff_id
                  AND tv.id = lt.id_tarif
                  AND tv.status = :statusTest
                  AND lt.date_activation <= :date
            ", [
            ':statusTest' => TariffVoip::STATUS_TEST,
            ':date' => $date->format(DateTimeZoneHelper::DATE_FORMAT)
        ]);

        foreach($query->queryAll() as $row) {
            $usage = UsageVoip::findOne(['id' => $row['usage_id']]);
            if (!$usage) {
                continue;
            }

            $info[] = $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": " . $usage->E164 . ", from: " . $usage->actual_from . ": set off";

            $model = new UsageVoipEditForm();
            $model->initModel($usage->clientAccount, $usage);
            $model->disconnecting_date = $yesterday->format(DateTimeZoneHelper::DATE_FORMAT);
            $model->status = UsageInterface::STATUS_WORKING;
            $model->edit();
        }

        return $info;
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function actionCheckVoipDayDisable()
    {
        $now = new DateTime('now');
        echo PHP_EOL . 'start ' . $now->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $isDayBlockExp = new Expression('voip_limit_day != 0 AND amount_day_sum < -voip_limit_day');
        $isMNBlockExp = new Expression('voip_limit_mn_day != 0 AND amount_mn_day_sum < -voip_limit_mn_day');

        $lockQuery = (new Query())
            ->select(['cc.client_id', 'voip_limit_day', 'amount_day_sum','voip_limit_mn_day', 'amount_mn_day_sum','is_overran', 'is_mn_overran'])
            ->addSelect([
                'is_block_day' => $isDayBlockExp,
                'is_block_mn' => $isMNBlockExp
            ])
            ->from(['c' => 'billing.clients'])
            ->innerJoin(['cc' => 'billing.cached_counters'], 'c.id=cc.client_id')
            ->innerJoin(['cl' => 'billing.locks'], 'c.id=cl.client_id')
            ->where([
                'AND',
                ['OR', ['cl.is_overran' => true],['cl.is_mn_overran' => true]], // стоит флаг превышения лимита (is_overran - дневной общий, is_mn_overran - дневной МН)
                ['OR', $isDayBlockExp, $isMNBlockExp], // или вычисляем сами блокировку под дневному и/или МН
                ['c.voip_disabled' => false] // телефония не выключена
            ]);

        foreach ($lockQuery->each(100, Yii::$app->dbPgSlave) as $lock) {
            $client = ClientAccount::findOne($lock['client_id']);

            if (!$client->voip_disabled) {
                echo PHP_EOL . '...';
                $info = 'ЛС: ' . $lock['client_id'] . '; ';
                if ($lock['is_overran']) {
                    $info .= 'flag day limit block: limit:' . $lock['voip_limit_day'] . ' / value: ' . abs($lock['amount_day_sum']);
                } elseif ($lock['is_mn_overran']) {
                    $info .= 'flag MN limit block: limit:' . $lock['voip_limit_mn_day'] . ' / value: ' . abs($lock['amount_mn_day_sum']);
                } else {
                    $info .= 'no flag found (voip_limit_day: ' . $lock['voip_limit_day'] .
                        ' / amount_day_sum: ' . $lock['amount_day_sum'] .
                        ' / voip_limit_mn_day: ' . $lock['voip_limit_mn_day'] .
                        ' / amount_mn_day_sum: ' . $lock['amount_mn_day_sum'] .
                        ' / is_overran: ' . $lock['is_overran'] .
                        ' / is_mn_overran: ' . $lock['is_mn_overran'];
                }

                echo $info;
                Yii::info('[usage/check-voip-day-disable] ' . $info);
                $client->voip_disabled = 1;
                $client->save();
            }
        }

        return ExitCode::OK;
    }

    /**
     * Заполнение поля с эффективной ставкой НДС.
     */
    public function actionResetEffectiveVatRate()
    {
        ClientContract::dao()->resetAllEffectiveVATRate();
    }

    /**
     * Устанавливает начальные значения. Тарифы телефонии с НДС или без НДС
     */
    public function actionResetContractsVoipTax()
    {
        $contractsQuery = ClientContract::find();

        $count = 0;
        /** @var ClientContract $contract */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($contractsQuery->each() as $contract) {

                if (++$count % 100 == 0) {
                    $transaction->commit();
                    echo PHP_EOL;
                    $transaction = Yii::$app->db->beginTransaction();
                }

                // нужен только расчет нужного поля
                $contract->detachBehaviors();
                $contract->attachBehavior('SetTaxVoip', SetTaxVoip::class);
                $contract->isHistoryVersioning = false;

                $contract->trigger(ClientContract::TRIGGER_RESET_TAX_VOIP);

                if ($contract->isSetVoipWithTax === null) {
                    echo ".";
                    continue;
                }

                if (!$contract->save()) {
                    throw new ModelValidationException($contract);
                }

                echo $contract->isSetVoipWithTax ? '+' : '-';
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Устанавливает блокировку по неоплате счета
     */
    public function actionCheckBillOverdue()
    {
        echo PHP_EOL . date('r') . ": start";

        $dateTo = new DateTime();
        $dateTo->modify('-1 day');

        $dateFrom = new DateTime();
        $dateFrom->modify('-3 day');

        $billQuery = Bill::find()
            ->where([
                'between',
                'pay_bill_until',
                $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT),
                $dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
            ]);

        $count = 0;

        /** @var Bill $bill */
        foreach ($billQuery->each() as $bill) {
            $count++;
            try {
                $bill->trigger(Bill::TRIGGER_CHECK_OVERDUE);
                if ($bill->isSetPayOverdue !== null) {
                    if (!$bill->save()) {
                        throw new ModelValidationException($bill);
                    }

                    echo PHP_EOL . date('r') . ": " . $bill->bill_no . " " . ($bill->isSetPayOverdue ? "(+)" : "(-)");
                }
            }catch (\Exception $e) {
                Yii::error($e);
                echo PHP_EOL . $bill->bill_no . " " . $e->getMessage();
            }
        }

        echo PHP_EOL . date('r') . ": end. Count: " . $count;
    }

    /**
     * Проверяем правильность установки блокировки по не уплате счета
     */
    public function actionRecheckClientOverdue()
    {
        $query = Yii::$app->db->createCommand("SELECT bill_no
FROM
  (SELECT
     (SELECT bill_no
      FROM newbills b
      WHERE b.client_id = a.client_id
      ORDER BY bill_date
      LIMIT 1) AS bill_no,
     a.*,
     c.is_bill_pay_overdue
   FROM (SELECT
           client_id,
           max(is_pay_overdue) AS max_v,
           min(is_pay_overdue) AS min_v
         FROM newbills
         GROUP BY client_id) a, clients c
   WHERE a.client_id = c.id
   HAVING max_v != is_bill_pay_overdue
  ) a")->query();

        $count = 0;
        while ($billNo = $query->readColumn(0)) {
            $bill = Bill::findOne(['bill_no' => $billNo]);
            echo PHP_EOL . $count++ . ': ' . $bill->client_id;
            $bill->trigger(Bill::TRIGGER_CHECK_OVERDUE);
        }
    }

    /**
     * Генерация событий о блокировке через 7/3/1 день по трафику телефонии
     */
    public function actionCheckVoipBlockByTrafficAlert()
    {
        echo PHP_EOL . date("r");

        $reportDays = 30;

        $tz = new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
        $periodTo = new \DateTime('now', $tz);
        $periodTo->setTime(0, 0, 0);

        $periodFrom = clone $periodTo;
        $periodFrom->modify('-' . $reportDays . ' days');


        $callsByAccountId = CallsAggr::dao()->getCallCostByPeriod($periodFrom, $periodTo);

        $activeUuUsagesQuery = AccountTariff::find()
            ->select('client_account_id')
            ->distinct()
            ->where([
                'AND',
                ['service_type_id' => ServiceType::ID_VOIP],
                ['IS NOT', 'voip_number', null],
                ['IS NOT', 'tariff_period_id', null],
            ]);

        $activeUsages = UsageVoip::find()
            ->select('c.id')
            ->distinct()
            ->innerJoin(['c' => ClientAccount::tableName()], 'c.client = ' . UsageVoip::tableName() . '.client')
            ->actual()
            ->union($activeUuUsagesQuery);

        $clientIdsQuery = (new Query())
            ->select('id')
            ->from(['a' => $activeUsages]);

        $accountsQuery = ClientAccount::find()
            ->with('flag')
            ->where(['id' => $clientIdsQuery]);


        /** @var ClientAccount $account */
        foreach ($accountsQuery->each() as $account) {
            if (!isset($callsByAccountId[$account->id]) || $callsByAccountId[$account->id]) {
                continue;
            }

            $callsCost = $callsByAccountId[$account->id];

            if ($callsCost > -100) {
                continue;
            }

            $perDay = $callsCost / $reportDays;

            $balance = $account->billingCountersFastMass->amount_sum;
            $flag = $account->flag;

            if (!$flag) {
                $flag = new ClientFlag;
                $flag->account_id = $account->id;
                $flag->is_notified_7day = 0;
                $flag->is_notified_3day = 0;
                $flag->is_notified_1day = 0;
            }

            $sum7Day = $balance + ($perDay * 7);
            if (-$sum7Day > $account->credit) {
                $flag->is_notified_7day = 1;
            } else {
                $flag->is_notified_7day = 0;
            }

            $sum3Day = $balance + ($perDay * 3);
            if (-$sum3Day > $account->credit) {
                $flag->is_notified_3day = 1;
            } else {
                $flag->is_notified_3day = 0;
            }

            $sum1Day = $balance + ($perDay * 1);
            if (-$sum1Day > $account->credit) {
                $flag->is_notified_1day = 1;
            } else {
                $flag->is_notified_1day = 0;
            }

            if (!$flag->save()) {
                throw new ModelValidationException($flag);
            }

            if ($flag->isSetFlag) {
                if ($flag->is_notified_7day || $flag->is_notified_3day || $flag->is_notified_1day) {
                    echo PHP_EOL . "(+) ";
                } else {
                    echo PHP_EOL . "(-) ";
                }

                echo $account->id . ' ';
                echo (int)$flag->is_notified_7day . '/' . (int)$flag->is_notified_3day . '/' . (int)$flag->is_notified_1day;
                echo ' callsCost: ' . $callsCost . ', perDay: ' . $perDay . ', credit: ' . $account->credit . ', balance: ' . $balance;
            }
        }
    }
}
