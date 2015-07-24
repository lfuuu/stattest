<?php
namespace app\classes\voip;

use Yii;
use app\models\ClientAccount;

class VoipStatus {
    /** @var ClientAccount */
    private $account;
    private $client;
    private $counters;

    public function __construct(ClientAccount $account)
    {
        $this->account = $account;
    }


    public function loadVoipCounters()
    {
        $this->counters =
            array_merge(
                ['amount_sum'=>0, 'amount_day_sum'=>0,'amount_month_sum'=>0,'auto_disabled'=>false,'auto_disabled_local'=>false,'error'=>false],
                $this->client
            );
        try {
            $counters_reg =
                Yii::$app->dbPg->createCommand("
                    SELECT
                        CAST(amount_sum as NUMERIC(8,2)) as amount_sum,
                        CAST(amount_day_sum as NUMERIC(8,2)) as amount_day_sum,
                        CAST(amount_month_sum as NUMERIC(8,2)) as amount_month_sum
                    FROM billing.counters
                    WHERE client_id = :accountId",
                    [':accountId' => $this->account->id]
                )->queryOne();
            $counters_locks =
                Yii::$app->dbPg->createCommand("
                    SELECT voip_auto_disabled, voip_auto_disabled_local
                    FROM billing.locks
                    WHERE client_id = :accountId",
                    [':accountId' => $this->account->id]
                )->queryOne();
            $this->counters['amount_sum'] = $counters_reg['amount_sum'];
            $this->counters['amount_day_sum'] = $counters_reg['amount_day_sum'];
            $this->counters['amount_month_sum'] = $counters_reg['amount_month_sum'];
            if ($counters_locks['voip_auto_disabled'] == 't') $this->counters['auto_disabled'] = true;
            if ($counters_locks['voip_auto_disabled_local'] == 't') $this->counters['auto_disabled_local'] = true;
        } catch (\Exception $e) {
            $this->counters['error'] = true;
        }
        $this->counters['balance'] = $this->account->balance + $this->counters['amount_sum'];

        $this->counters['need_lock_limit_day'] = ($this->account->voip_credit_limit_day != 0 && -$this->counters['amount_day_sum'] > $this->account->voip_credit_limit_day);
        $this->counters['need_lock_limit_month'] = ($this->account->voip_credit_limit != 0 && -$this->counters['amount_month_sum'] > $this->account->voip_credit_limit);
        $this->counters['need_lock_credit'] = ($this->account->credit >= 0 && $this->counters['balance'] + $this->account->credit < 0);
        $this->counters['need_lock_flag'] = ($this->account->voip_disabled > 0);

        return $this->counters;
    }

    public function getWarnings()
    {
        $warnings = [];

        if ($this->counters['error'])
            $warnings[] = "Сервер статистики недоступен. Данные о балансе и счетчиках могут быть не верными";
        if ($this->counters['auto_disabled_local'])
            $warnings[] = "ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (Местная связь)";
        if ($this->counters['auto_disabled'])
            $warnings[] = "ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (МГ, МН, Местные мобильные)";
        if ($this->counters['need_lock_limit_day'])
            $warnings[] = 'Превышен дневной лимит: '.(-$this->counters['amount_day_sum']).' > '.$this->account->voip_credit_limit_day;
        if ($this->counters['need_lock_limit_month'])
            $warnings[] = 'Превышен месячный лимит: '.(-$this->counters['amount_month_sum']).' > '.$this->account->voip_credit_limit;
        if ($this->counters['need_lock_credit'])
            $warnings[] = 'Превышен лимит кредита: '.$this->counters['balance'].' < -'.$this->account->credit;

        return $warnings;
    }
}