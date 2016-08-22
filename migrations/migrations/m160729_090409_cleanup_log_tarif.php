<?php

class m160729_090409_cleanup_log_tarif extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("DELETE l FROM usage_voip as v, log_tarif as l where v.id = l.id_service and l.service = 'usage_voip' and l.date_activation > v.actual_to");
        $this->alterColumn(\app\models\EventQueue::tableName(),  'next_start', $this->timestamp()->notNull()->defaultValue('2000-01-01 00:00:00'));
        $this->alterColumn(\app\models\EventQueue::tableName(), 'log_error', $this->text());
    }

    public function down()
    {
        //nothing
    }
}