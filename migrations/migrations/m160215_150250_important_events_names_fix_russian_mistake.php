<?php

class m160215_150250_important_events_names_fix_russian_mistake extends \app\classes\Migration
{
    public function up()
    {
        $this->update('important_events_names', ['value' => 'Перемещение: Контракт'], ['code' => 'contract_transfer']);
    }

    public function down()
    {
    }
}