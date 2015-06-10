<?php
namespace app\classes\bill;

use app\classes\Assert;
use app\models\ClientAccount;
use app\models\Transaction;
use app\models\Usage;
use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveRecord;

abstract class Biller
{
    const PERIOD_ONCE = 'once';
    const PERIOD_MONTH = 'month';
    const PERIOD_3_MONTH = '3mon';
    const PERIOD_6_MONTH = '6mon';
    const PERIOD_YEAR = 'year';

    /** @var ActiveRecord */
    public $usage;
    /** @var ClientAccount */
    public $clientAccount;
    /** @var Transaction[] */
    protected $transactions = [];


    /** @var DateTimeZone */
    public $timezone;

    /** @var DateTime */
    public $billerDate;
    /** @var DateTime */
    public $billerPeriodFrom;
    /** @var DateTime */
    public $billerPeriodTo;
    /** @var DateTime */
    public $usageActualFrom;
    /** @var DateTime */
    public $usageActualTo;
    /** @var DateTime */
    public $billerActualFrom;
    /** @var DateTime */
    public $billerActualTo;

    public function __construct(Usage $usage, DateTime $date, ClientAccount $clientAccount)
    {
        $this->usage = $usage;
        $this->clientAccount = $clientAccount;

        $this->timezone = $this->clientAccount->timezone;

        $this->setupBillerDate($date);
        $this->setupBillerPeriod();

        $this->setupUsageActualPeriod();
        $this->setupBillerActualPeriod();
    }

    protected function setupBillerDate(DateTime $date)
    {
        $this->billerDate = new DateTime();
        $this->billerDate->setTimezone($this->timezone);
        $this->billerDate->setDate($date->format('Y'), $date->format('m'), $date->format('d'));
        $this->billerDate->setTime($date->format('H'), $date->format('i'), $date->format('s'));
    }

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

    protected function setupUsageActualPeriod()
    {
        if ($this->usage->hasAttribute('actual_from')) {
            $this->usageActualFrom = new DateTime($this->usage->actual_from, $this->timezone);
        }

        Assert::isObject($this->usageActualFrom);


        if ($this->usage->hasAttribute('actual_to')) {
            $this->usageActualTo = new DateTime($this->usage->actual_to, $this->timezone);
            $this->usageActualTo->setTime(23, 59, 59);
        }

        Assert::isObject($this->usageActualTo);
    }

    protected function setupBillerActualPeriod()
    {
        $this->billerActualFrom =
            $this->usageActualFrom > $this->billerPeriodFrom
                ? clone $this->usageActualFrom
                : clone $this->billerPeriodFrom;

        $this->billerActualTo =
            $this->usageActualTo < $this->billerPeriodTo
                ? clone $this->usageActualTo
                : clone $this->billerPeriodTo;
    }

    protected function addPackage(BillerPackage $package)
    {
        $transaction = $package->createTransaction();
        if ($transaction) {
            $this->addTransaction($transaction);
        }
    }

    protected function addTransaction(Transaction $transaction)
    {
        $this->transactions[] = $transaction;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    public function process($connecting = true, $periodical = true, $resource = true)
    {
        if ($this->beforeProcess() === false) {
            return $this;
        }

        if ($connecting) {
            $this->processConnecting();
        }

        if ($periodical) {
            $this->processPeriodical();
        }

        if ($resource) {
            $this->processResource();
        }

        return $this;
    }

    protected function beforeProcess()
    {
        return true;
    }

    protected function processConnecting() {

    }

    protected function processPeriodical() {

    }

    protected function processResource() {

    }

    public function getTranslateFilename()
    {
        return 'biller';
    }

    public function getPeriodTemplate($period)
    {
        if ($period == 'once') {
            return 'date_once';
        } elseif ($period == 'month') {
            return 'date_range_full';
        } elseif ($period == 'year') {
            return 'date_range_with_year';
        }
    }
    protected function getContractInfo()
    {
        $dateTs = $this->billerDate->getTimestamp();

        $contract =
            Yii::$app->db->createCommand('
                select
                    contract_no as no,
                    unix_timestamp(contract_date) as date
                from
                    client_document
                where
                        client_id = :clientAccountId
                        and contract_date <= FROM_UNIXTIME(:dateTs)
                order by is_active desc, contract_date desc, id desc
                limit 1 ',
                [':clientAccountId' => $this->clientAccount->id, ':dateTs' => $dateTs]
            )
                ->queryOne();

        if ($contract) {
            return Yii::t('biller', 'by_agreement', [
                'contract_no' => $contract['no'],
                'contract_date' => $contract['date']
            ], $this->clientAccount->contragent->country->lang);
        } else {
            return '';
        }
    }


}