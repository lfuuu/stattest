<?php

/**
 * Class m230630_154919_contragent_lk_status
 */
class m230630_154919_contragent_lk_status extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\ClientContragent::tableName(), 'lk_status', $this->string(64));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'lk_status');
    }
}