<?php
class VoipStatus {
  private $client;
  private $counters;

  public function loadClient($client){
    global $db;
    if (intval($client) > 0){
      $this->client = $db->GetRow("SELECT id, client, voip_disabled, credit, balance, voip_credit_limit, voip_credit_limit_day FROM clients where id='".intval($client)."'");
    }else{
      $this->client = $db->GetRow("SELECT id, client, voip_disabled, credit, balance, voip_credit_limit, voip_credit_limit_day FROM clients where client='".$client."'");
    }
  }

  public function loadVoipCounters(){
    global $pg_db;
    if ($this->client === 0) return array();

    $this->counters = array_merge(
      array('amount_sum'=>0, 'amount_day_sum'=>0,'amount_month_sum'=>0,'auto_disabled'=>false,'auto_disabled_local'=>false,'error'=>false),
      $this->client );
      try {
        $counters_reg = $pg_db->GetRow("SELECT   CAST(amount_sum as NUMERIC(8,2)) as amount_sum, CAST(amount_day_sum as NUMERIC(8,2)) as amount_day_sum, CAST(amount_month_sum as NUMERIC(8,2)) as amount_month_sum, voip_auto_disabled, voip_auto_disabled_local
                                           FROM billing.counters
                                           WHERE client_id='".$this->counters["id"]."'");
        $this->counters['amount_sum'] = $counters_reg['amount_sum'];
        $this->counters['amount_day_sum'] = $counters_reg['amount_day_sum'];
        $this->counters['amount_month_sum'] = $counters_reg['amount_month_sum'];
        if ($counters_reg['voip_auto_disabled'] == 't') $this->counters['auto_disabled'] = true;
        if ($counters_reg['voip_auto_disabled_local'] == 't') $this->counters['auto_disabled_local'] = true;
      } catch (Exception $e) {
        $this->counters['error'] = true;
      }
    $this->counters['balance'] = $this->counters['balance'] - $this->counters['amount_sum'];

    $this->counters['need_lock_limit_day'] = ($this->counters['voip_credit_limit_day'] != 0 && $this->counters['amount_day_sum'] > $this->counters['voip_credit_limit_day']);
    $this->counters['need_lock_limit_month'] = ($this->counters['voip_credit_limit'] != 0 && $this->counters['amount_month_sum'] > $this->counters['voip_credit_limit']);
    $this->counters['need_lock_credit'] = ($this->counters['credit'] >= 0 && $this->counters['balance'] + $this->counters['credit'] < 0);
    $this->counters['need_lock_flag'] = ($this->counters['voip_disabled'] > 0);

    return $this->counters;
  }

  public function showCountersWarning()
  {
    if ($this->counters['error']) trigger_error("Сервер статистики недоступен. Данные о балансе и счетчиках могут быть не верными");
    if ($this->counters['auto_disabled_local']) trigger_error("ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (Местная связь)");
    if ($this->counters['auto_disabled']) trigger_error("ТЕЛЕФОНИЯ ЗАБЛОКИРОВАНА (МГ, МН, Местные мобильные)");
    if ($this->counters['need_lock_limit_day']) trigger_error('Превышен дневной лимит: '.$this->counters['amount_day_sum'].' > '.$this->counters['voip_credit_limit_day']);
    if ($this->counters['need_lock_limit_month']) trigger_error('Превышен месячный лимит: '.$this->counters['amount_month_sum'].' > '.$this->counters['voip_credit_limit']);
    if ($this->counters['need_lock_credit']) trigger_error('Превышен лимит кредита: '.$this->counters['balance'].' < -'.$this->counters['credit']);
  }
}