<?php

/**
 * Class m230619_095742_contragent_is_lk_first
 */
class m230619_095742_contragent_is_lk_first extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\ClientContragent::tableName(), 'is_lk_first', $this
            ->integer()
            ->notNull()
            ->defaultValue(0)
            ->comment('Is the LK the main data provider')
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'is_lk_first');
    }
}
