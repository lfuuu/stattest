<?php
namespace app\classes\voip;

use Yii;
use app\models\ClientAccount;

class VoipStatus {
    /** @var ClientAccount */
    private $account;

    private $amount_sum = null;
    private $amount_day_sum = null;
    private $amount_month_sum = null;
    private $auto_disabled = false;
    private $auto_disabled_local = false;

    private $error = false;

    private $balance = null;

    public static function create(ClientAccount $account)
    {
        return new static($account);
    }

    private function __construct(ClientAccount $account)
    {
        $this->account = $account;
    }


    public function getRealtimeBalance()
    {
        $this->loadCounters();

        if ($this->error) {
            return 'NaN';
        }

        return $this->balance;
    }

    public function getWarnings()
    {
        $this->loadCounters();

        if ($this->error) {
            return ['Сервер статистики недоступен. Данные о балансе и счетчиках могут быть не верными'];
        }

        $this->loadLocks();

        if ($this->error) {
            return ['Сервер статистики недоступен. Данные о балансе и счетчиках могут быть не верными'];
        }

        $need_lock_limit_day = ($this->account->voip_credit_limit_day != 0 && - $this->amount_day_sum > $this->account->voip_credit_limit_day);
        $need_lock_limit_month = ($this->account->voip_credit_limit != 0 && - $this->amount_month_sum > $this->account->voip_credit_limit);
        $need_lock_credit = ($this->account->credit >= 0 && $this->balance + $this->account->credit < 0);
        $need_lock_flag = ($this->account->voip_disabled > 0);

        $warnings = [];

        if ($this->auto_disabled_local)
            $warnings[] = "ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (Местная связь)";
        if ($this->auto_disabled)
            $warnings[] = "ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (МГ, МН, Местные мобильные)";
        if ($need_lock_limit_day)
            $warnings[] = 'Превышен дневной лимит: '.(-$this->amount_day_sum).' > '.$this->account->voip_credit_limit_day;
        if ($need_lock_limit_month)
            $warnings[] = 'Превышен месячный лимит: '.(-$this->amount_month_sum).' > '.$this->account->voip_credit_limit;
        if ($need_lock_credit)
            $warnings[] = 'Превышен лимит кредита: '.$this->balance.' < -'.$this->account->credit;

        return $warnings;
    }

    private function loadCounters()
    {
        try {
            $counters =
                Yii::$app->dbPg->createCommand("
                    SELECT
                        CAST(amount_sum as NUMERIC(8,2)) as amount_sum,
                        CAST(amount_day_sum as NUMERIC(8,2)) as amount_day_sum,
                        CAST(amount_month_sum as NUMERIC(8,2)) as amount_month_sum
                    FROM billing.counters
                    WHERE client_id = :accountId",
                    [':accountId' => $this->account->id]
                )->queryOne();

            $this->amount_sum =         $counters['amount_sum'];
            $this->amount_day_sum =     $counters['amount_day_sum'];
            $this->amount_month_sum =   $counters['amount_month_sum'];
            $this->balance =            $this->account->balance + $this->amount_sum;

        } catch (\Exception $e) {
            $this->error = true;
        }

    }

    private function loadLocks()
    {
        try {
            $locks =
                Yii::$app->dbPg->createCommand("
                    SELECT voip_auto_disabled, voip_auto_disabled_local
                    FROM billing.locks
                    WHERE client_id = :accountId",
                    [':accountId' => $this->account->id]
                )->queryOne();

            $this->auto_disabled =          $locks['voip_auto_disabled'] == 't';
            $this->auto_disabled_local =    $locks['voip_auto_disabled_local'] == 't';

        } catch (\Exception $e) {
            $this->error = true;
        }
    }


}