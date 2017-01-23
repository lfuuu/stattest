<?php
use app\models\Bill;

/**
 * Class m170120_164052_bill_organization
 */
class m170120_164052_bill_organization extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Bill::tableName(), 'organization_id', $this->integer()->defaultValue(null));
        $this->addForeignKey('fk-organization_id', Bill::tableName(), 'organization_id', \app\models\Organization::tableName(), 'id', 'RESTRICT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Bill::tableName(), 'organization_id');
    }
}
