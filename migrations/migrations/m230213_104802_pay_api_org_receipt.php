<?php

/**
 * Class m230213_104802_pay_api_org_receipt
 */
class m230213_104802_pay_api_org_receipt extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\PaymentApiChannel::tableName(), 'check_organization_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\PaymentApiChannel::tableName(), 'check_organization_id');
    }
}
