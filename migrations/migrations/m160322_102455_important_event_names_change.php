<?php

class m160322_102455_important_event_names_change extends \app\classes\Migration
{
    public function up()
    {
        $this->update('important_events_names', ['value' => 'Уведомление: Зачисление средств'], ['code' => 'add_pay_notif']);
    }

    public function down()
    {
        $this->update('important_events_names', ['value' => 'Зачисление средств'], ['code' => 'add_pay_notif']);
    }
}