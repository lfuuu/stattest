<?php

use app\models\Lead;
use app\models\SaleChannel;

/**
 * Class m180410_160243_lead_sale_channel
 */
class m180410_160243_lead_sale_channel extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Lead::tableName(), 'sale_channel_id', $this->integer()->unsigned());
        $this->addForeignKey('fk-' . Lead::tableName().'-sale_channel_id-'. SaleChannel::tableName().'-id', Lead::tableName(), 'sale_channel_id', SaleChannel::tableName(), 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Lead::tableName(), 'sale_channel_id');
    }
}
