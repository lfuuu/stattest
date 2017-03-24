<?php
namespace app\classes\bill;

use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Transaction;
use app\models\usages\UsageInterface;
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

    protected $isOnlyConnecting;
    protected $isConnecting;
    protected $isPeriodical;
    protected $isResource;

    protected $forecastCoefficient;

    /**
     * Biller constructor.
     *
     * @param UsageInterface $usage
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     */
    public function __construct(UsageInterface $usage, DateTime $date, ClientAccount $clientAccount)
    {
        $this->usage = $usage;
        $this->clientAccount = $clientAccount;

        $this->timezone = $this->clientAccount->timezone;

        $this->setupBillerDate($date);
        $this->setupBillerPeriod();

        $this->setupUsageActualPeriod();
        $this->setupBillerActualPeriod();
    }

    /**
     * Установка дату биллера
     *
     * @param DateTime $date
     */
    protected function setupBillerDate(DateTime $date)
    {
        $this->billerDate = new DateTime();

        $this->billerDate->setTimezone($this->timezone);
        $this->billerDate->setDate($date->format('Y'), $date->format('m'), $date->format('d'));
        $this->billerDate->setTime($date->format('H'), $date->format('i'), $date->format('s'));
    }

    /**
     * Установка период биллера
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
     * Установка актуального периода услуг
     */
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

    /**
     * Установка актуального периода биллера
     */
    protected function setupBillerActualPeriod()
    {
        $this->billerActualFrom = $this->usageActualFrom > $this->billerPeriodFrom ?
            clone $this->usageActualFrom :
            clone $this->billerPeriodFrom;

        $this->billerActualTo = $this->usageActualTo < $this->billerPeriodTo ?
            clone $this->usageActualTo :
            clone $this->billerPeriodTo;
    }

    /**
     * Добавление пакета
     *
     * @param BillerPackage $package
     */
    protected function addPackage(BillerPackage $package)
    {
        $transaction = $package->createTransaction();
        if ($transaction) {
            $this->addTransaction($transaction);
        }
    }

    /**
     * Добавление транзакции
     *
     * @param Transaction $transaction
     */
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

    /**
     * Запуск рассчета
     *
     * @param bool $onlyConnecting
     * @param bool $connecting
     * @param bool $periodical
     * @param bool $resource
     * @return $this
     */
    public function process($onlyConnecting = false, $connecting = true, $periodical = true, $resource = true)
    {
        $this->isOnlyConnecting = $onlyConnecting;
        $this->isConnecting = $connecting;
        $this->isPeriodical = $periodical;
        $this->isResource = $resource;

        if ($this->beforeProcess() === false) {
            return $this;
        }

        if ($onlyConnecting) {
            if ($this->usage->status == 'connecting') {

                if ($connecting) {
                    $this->processConnecting();
                }

                if ($periodical) {
                    $this->processPeriodical();
                }

                if ($resource) {
                    $this->processResource();
                }
            }
        } else {

            if ($connecting) {
                $this->processConnecting();
            }

            if ($periodical) {
                $this->processPeriodical();
            }

            if ($resource) {
                $this->processResource();
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function beforeProcess()
    {
        return true;
    }

    /**
     * Расчет подключаемых значений
     */
    protected function processConnecting()
    {

    }

    /**
     * Расчет абон.платы
     */
    protected function processPeriodical()
    {

    }

    /**
     * Расчет ресурсов
     */
    protected function processResource()
    {

    }

    /**
     * @return string
     */
    public function getTranslateFilename()
    {
        return 'biller';
    }

    /**
     * Шаблон периода
     *
     * @param string $period
     * @param DateTime $from
     * @param DateTime $to
     * @return string
     */
    public function getPeriodTemplate($period, DateTime $from, DateTime $to)
    {

        if ($period == 'once') {
            return 'date_once';
        } elseif ($from->format('m') == $to->format('m') && $from->format('Y') == $to->format('Y')) {
            return 'date_range_month';
        } elseif ($from->format('Y') == $to->format('Y')) { // from->m != to->m
            return 'date_range_year';
        } else {
            return 'date_range_full';
        }
    }

    /**
     * Получение номера договора ЛС
     *
     * @return string
     */
    protected function getContractInfo()
    {
        $contract = $this->clientAccount->contract->getContractInfo($this->billerDate);

        if ($contract) {
            return Yii::t('biller', 'by_agreement', [
                'contract_no' => $contract->contract_no,
                'contract_date' => (new \DateTime($contract->contract_date,
                    new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))->getTimestamp()
            ], $this->clientAccount->contragent->country->lang);
        } else {
            return '';
        }
    }

    /**
     * Установка коэффициента прогноза
     *
     * @param float $coefficient
     */
    public function setForecastCoefficient($coefficient)
    {
        $this->forecastCoefficient = $coefficient;
    }

    /**
     * Применение коэффициента прогноза к значению
     *
     * @param float|array $value
     */
    protected function applyForecastCoefficient(&$value)
    {
        if (!$this->forecastCoefficient) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $key => &$val) {
                $this->applyForecastCoefficient($val);
            }
        } else {
            $value *= $this->forecastCoefficient;
        }
    }
}
