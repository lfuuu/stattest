<?php

/**
 * Class m201011_161232_entry_point_price_level
 */
class m201011_161232_entry_point_price_level extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\EntryPoint::tableName(), 'price_level', $this->integer()->notNull()->defaultValue(\app\models\ClientAccount::DEFAULT_PRICE_LEVEL));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\EntryPoint::tableName(), 'price_level');
    }
}
