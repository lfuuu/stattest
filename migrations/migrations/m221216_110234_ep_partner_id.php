<?php

/**
 * Class m221216_110234_ep_partner_id
 */
class m221216_110234_ep_partner_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\EntryPoint::tableName(), 'partner_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\EntryPoint::tableName(), 'partner_id');
    }
}
