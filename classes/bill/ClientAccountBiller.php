<?php
namespace app\classes\bill;

use app\helpers\DateTimeZoneHelper;
use app\models\UsageCallChat;
use app\modules\uu\models\AccountTariff;
use Yii;
use DateTime;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\UsageEmails;
use app\models\Transaction;
use app\models\usages\UsageInterface;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\models\UsageTrunk;
use app\models\UsageWelltime;

class ClientAccountBiller
{
    /** @var DateTime */
    protected $billerDate;
    /** @var DateTime */
    public $billerPeriodFrom;
    /** @var DateTime */
    public $billerPeriodTo;

    /** @var ClientAccount */
    protected $clientAccount;
    /** @var Transaction[] */
    protected $transactions = [];
    /** @var BillLine[] */
    protected $billLines = [];

    protected $onlyConnecting;
    protected $connecting;
    protected $periodical;
    protected $resource;

    /** @var null|float */
    protected $forecastCoefficient;

    /**
     * Создание биллера ЛС
     *
     * @param ClientAccount $clientAccount
     * @param DateTime $date
     * @param bool $onlyConnecting
     * @param bool $connecting
     * @param bool $periodical
     * @param bool $resource
     * @param null $forecastCoefficient
     * @return ClientAccountBiller
     */
    public static function create(
        ClientAccount $clientAccount,
        DateTime $date,
        $onlyConnecting = false,
        $connecting = true,
        $periodical = true,
        $resource = true,
        $forecastCoefficient = null
    ) {
        return new static($clientAccount, $date, $onlyConnecting, $connecting, $periodical, $resource, $forecastCoefficient);
    }

    /**
     * ClientAccountBiller constructor.
     *
     * @param ClientAccount $clientAccount
     * @param DateTime $date
     * @param bool $onlyConnecting
     * @param bool $connecting
     * @param bool $periodical
     * @param bool $resource
     * @param null|float $forecastCoefficient
     */
    protected function __construct(
        ClientAccount $clientAccount,
        DateTime $date,
        $onlyConnecting,
        $connecting,
        $periodical,
        $resource,
        $forecastCoefficient
    ) {
        $this->billerDate = $date;
        $this->clientAccount = $clientAccount;
        $this->onlyConnecting = $onlyConnecting;
        $this->connecting = $connecting;
        $this->periodical = $periodical;
        $this->resource = $resource;
        $this->forecastCoefficient = $forecastCoefficient;

        $this->setupBillerPeriod();
    }

    /**
     * Установка периода биллера
     */
    protected function setupBillerPeriod()
    {
        $year = $this->billerDate->format('Y');
        $month = $this->billerDate->format('m');

        $this->billerPeriodFrom = clone $this->billerDate;
        $this->billerPeriodFrom->setDate($year, $month, 1);
        $this->billerPeriodFrom->setTime(0, 0, 0);

        $this->billerPeriodTo = clone $this->billerDate;
        $this->billerPeriodTo->setDate($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
        $this->billerPeriodTo->setTime(23, 59, 59);
    }

    /**
     * Запуск расчета
     *
     * @return $this
     */
    public function process()
    {
        $this->createTransactions();
        $this->saveTransactions();
        return $this;
    }

    /**
     * Создание транзакций
     *
     * @return $this
     */
    public function createTransactions()
    {
        $this->transactions = [];

        if ($this->onlyConnecting) {
            $status = 'connecting';
        } else {
            $status = 'working';
        }

        $this->_processUsages(
            UsageIpPorts::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageVoip::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageVoipPackage::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageTrunk::find()
                ->andWhere(['client_account_id' => $this->clientAccount->id])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->andWhere(['<', 'id', AccountTariff::DELTA])
                ->all()
        );

        $this->_processUsages(
            UsageVirtpbx::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageExtra::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageWelltime::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageEmails::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageSms::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );

        $this->_processUsages(
            UsageCallChat::find()
                ->andWhere(['client' => $this->clientAccount->client])
                ->andWhere(['status' => $status])
                ->andWhere('actual_to >= :from', [':from' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->all()
        );


        return $this;
    }

    /**
     * Сохранение транзакций
     *
     * @return $this
     * @throws \Exception
     */
    public function saveTransactions()
    {
        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $transactionTypes = [];

            if ($this->connecting) {
                $transactionTypes[] = Transaction::TYPE_CONNECTING;
            }

            if ($this->periodical) {
                $transactionTypes[] = Transaction::TYPE_PERIODICAL;
            }

            if ($this->resource) {
                $transactionTypes[] = Transaction::TYPE_RESOURCE;
            }

            $query = Transaction::find()
                ->andWhere([
                    'client_account_id' => $this->clientAccount->id,
                    'source' => Transaction::SOURCE_STAT,
                    'billing_period' => $this->billerPeriodFrom->format(DateTimeZoneHelper::DATE_FORMAT),
                    'transaction_type' => $transactionTypes,
                ])
                ->orderBy('id');

            /** @var Transaction[] $transactions */
            $transactions = $query->all();

            $existsTransactionsByKey = [];
            foreach ($transactions as $transaction) {
                $key = $transaction->service_type . '_' . $transaction->service_id . '_' . $transaction->package_id . '_' . $transaction->transaction_type;
                if (!isset($existsTransactionsByKey[$key])) {
                    $existsTransactionsByKey[$key] = [];
                }

                $existsTransactionsByKey[$key][] = $transaction;
            }

            $tochedBillIds = [];

            foreach ($this->transactions as $k => $transaction) {
                $key = $transaction->service_type . '_' . $transaction->service_id . '_' . $transaction->package_id . '_' . $transaction->transaction_type;
                if (isset($existsTransactionsByKey[$key])) {
                    $existsTransaction = array_shift($existsTransactionsByKey[$key]);
                    if (empty($existsTransactionsByKey[$key])) {
                        unset($existsTransactionsByKey[$key]);
                    }

                    Transaction::dao()->copy($transaction, $existsTransaction);

                    if ($existsTransaction->bill_id && $existsTransaction->bill_line_id) {
                        if (!isset($tochedBillIds[$existsTransaction->bill_id])) {
                            $tochedBillIds[$existsTransaction->bill_id] = $existsTransaction->bill_id;
                        }

                        Transaction::dao()->updateBillLine($existsTransaction);
                    }

                    $existsTransaction->save();
                } else {
                    $transaction->save();
                }
            }

            foreach ($existsTransactionsByKey as $transactions) {
                foreach ($transactions as $transaction) {

                    if ($transaction->bill_id && $transaction->bill_line_id) {
                        if (!isset($tochedBillIds[$transaction->bill_id])) {
                            $tochedBillIds[$transaction->bill_id] = $transaction->bill_id;
                        }

                        Transaction::dao()->deleteBillLine($transaction);
                    }

                    $transaction->delete();
                }
            }

            foreach ($tochedBillIds as $billId) {
                $bill = Bill::findOne($billId);
                Bill::dao()->recalcBill($bill);
            }

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Рассчет усулг
     *
     * @param array $usages
     */
    private function _processUsages(array $usages)
    {
        foreach ($usages as $usage) {
            $this->_processUsage($usage);
        }
    }

    /**
     * Расчет услуги
     *
     * @param UsageInterface $usage
     */
    private function _processUsage(UsageInterface $usage)
    {
        $biller = $usage->getBiller($this->billerDate, $this->clientAccount);

        if ($this->forecastCoefficient) {
            $biller->setForecastCoefficient($this->forecastCoefficient);
        }

        $transactions = $biller
            ->process($this->onlyConnecting, $this->connecting, $this->periodical, $this->resource)
            ->getTransactions();
        $this->transactions = array_merge($this->transactions, $transactions);
    }

    /**
     * @return bool
     */
    public function isOnlyConnecting()
    {
        return $this->onlyConnecting;
    }

    /**
     * @return bool
     */
    public function isConnecting()
    {
        return $this->connecting;
    }

    /**
     * @return bool
     */
    public function isPeriodical()
    {
        return $this->periodical;
    }

    /**
     * @return bool
     */
    public function isResource()
    {
        return $this->resource;
    }
}
