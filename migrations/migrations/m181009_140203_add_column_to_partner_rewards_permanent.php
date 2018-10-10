<?php

use app\models\PartnerRewardsPermanent;

/**
 * Class m181009_140203_add_column_to_partner_rewards_permanent
 */
class m181009_140203_add_column_to_partner_rewards_permanent extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(PartnerRewardsPermanent::tableName(), 'partner_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(PartnerRewardsPermanent::tableName(), 'partner_id');
    }
}
