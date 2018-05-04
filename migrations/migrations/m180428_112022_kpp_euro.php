<?php

use app\models\ClientContragent;

/**
 * Class m180428_112022_kpp_euro
 */
class m180428_112022_kpp_euro extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientContragent::tableName(), 'tax_registration_reason', $this->string(1024)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientContragent::tableName(), 'tax_registration_reason');
    }
}
