<?php

class m160315_075324_append_index_tarifs_voip extends \app\classes\Migration
{
    public function up()
    {
        $this->createIndex(
            'is_testing_status_month_line_month_min_payment',
            'tarifs_voip',
            [
                'is_testing',
                'status',
                'month_line',
                'month_min_payment'
            ]
        );
    }

    public function down()
    {
        $this->dropIndex('is_testing_status_month_line_month_min_payment', 'tarifs_voip');
    }
}