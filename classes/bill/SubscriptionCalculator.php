<?php
namespace app\classes\bill;

use Yii;
use app\models\ClientAccount;
use app\models\ClientCounter;
use app\classes\Assert;

/**
 * Класс, рассчитываеющий данные для реалтайм баланса.
 */
class SubscriptionCalculator
{
    /** @var ClientAccount */
    private $clientAccount;
    private $periods;

    /**
     * Сумма, которую можно прибавить к балансу по счетам 
     * и получить баланс с учетом начисленной абонентки
     */
    public $sum = 0;

    /**
     * Сумма абонентки в последнем выставленном счете
     */
    public $sumLastMonth = 0;

    /**
     * Начисленная абонентка, по дням
     */
    public $sumCalcualtedAbon = 0;

    /**
     * @return SubscriptionCalculator
     */
    public static function create()
    {
        return new static;
    }

    public function setClientAccountId($clientAccountId)
    {
        $this->clientAccount = ClientAccount::findOne($clientAccountId);

        Assert::isNotEmpty($this->clientAccount);

        return $this;
    }


    /**
     * Основная функция расчета абонентки
     *
     * @param null/int Дата последнего выставленного счета
     */
    public function calculate($lastAccountDate = null)
    {
        if (!$lastAccountDate)
        {
            return $this;
        }


        $lastAccountDate = strtotime($lastAccountDate);
        $today = strtotime("00:00:00");


        if (!$lastAccountDate || !$today || $lastAccountDate > $today)
            return $this;

        if ((($today - $lastAccountDate) / 86400) > 62) // если небыло счетов больше 2х месяцев
            return $this;


        //Расчет абонентки, выставленной в последнем счете
        $writeOffEnd = strtotime("last day of this month", $lastAccountDate);
        $this->sumLastMonth = $this->calculatePeriod($lastAccountDate, $writeOffEnd);

        //Расчет начисленной абонентки
        $firstDay = strtotime("first day of this month", time());
        $today = strtotime("00:00:00", time());
        $this->sumCalcualtedAbon = $this->calculatePeriod($firstDay, $today);

        $this->sum = -$this->sumLastMonth + $this->sumCalcualtedAbon;

        return $this;
    }

    private function calculatePeriod($firstDate, $endDate)
    {
        $this->setPeriod($firstDate, $endDate);

        $sum_total = 0;

        $services = get_all_services($this->clientAccount->client, $this->clientAccount->id);

        foreach($services as $idx => $service)
        {
            $s = $this->getPrebillAmountForService($service);
            $sum_total += $s;
        }

        return $sum_total * (1 + $this->clientAccount->getTaxRate());
    }

    private function getPrebillAmountForService($service)
    {
        $result = 0;

        $s = \ServiceFactory::Get($service,$this->clientAccount->toArray());
        foreach ($this->periods as $p)
        {
            $s->SetDate($p[0], $p[1]);
            $v = $s->getServicePreBillAmount();
            $result += $v;
        }

        return $result;
    }

    private function setPeriod($firstDate, $endDate)
    {
        //разбиение на периоды вызвано ограничениями в функции рассчета процентного отношения услуги к периоду. Она работает только в рамках одного месяца.
        $secondDate = min(strtotime("last day of this month", $firstDate), $endDate);

        $periods[] = [$firstDate, $secondDate, date("Y-m-d", $firstDate), date("Y-m-d", $secondDate)];

        while($secondDate != $endDate)
        {
            $firstDate = strtotime("first day of next month", $firstDate);
            $secondDate = min(strtotime("last day of this month", $firstDate), $endDate);
            $period = [$firstDate, $secondDate, date("Y-m-d", $firstDate), date("Y-m-d", $secondDate)];

            $periods[] = $period;
        }

        $this->periods = $periods;

        return $this;
    }

    /**
     * Функция сохранения вычисленных значений в базу
     */
    public function save()
    {
        $row = ClientCounter::findOne($this->clientAccount->id);
        if (!$row) {
            $row = new ClientCounter();
            $row->client_id = $this->clientAccount->id;
        }

        $row->subscription_rt_balance = $this->sum;
        $row->subscription_rt_last_month = $this->sumLastMonth;
        $row->subscription_rt = $this->sumCalcualtedAbon;

        $row->save();
    }

}

