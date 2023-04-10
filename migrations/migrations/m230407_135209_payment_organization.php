<?php

/**
 * Class m230407_135209_payment_organization
 */
class m230407_135209_payment_organization extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Payment::tableName(), 'organization_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Payment::tableName(), 'organization_id');
    }
}
