<?php
use app\models\Bill;
use app\models\ClientAccount;
use yii\db\Expression;

/**
 * Class m170307_145026_bill_pay_to_date
 */
class m170307_145026_bill_pay_to_date extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientAccount::tableName(), 'pay_bill_until_days', $this->integer()->notNull()->defaultValue(30));
        $this->addColumn(ClientAccount::tableName(), 'is_bill_pay_overdue', $this->integer()->defaultValue(0));
        $this->addColumn(Bill::tableName(), 'pay_bill_until', $this->date());
        $this->addColumn(Bill::tableName(), 'is_pay_overdue', $this->integer()->defaultValue(0));
        $this->createIndex('idx-pay_bill_until', Bill::tableName(), 'pay_bill_until');
        $this->update(Bill::tableName(), ['pay_bill_until' => new Expression('bill_date + interval 30 day')]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientAccount::tableName(), 'pay_bill_until_days');
        $this->dropColumn(ClientAccount::tableName(), 'is_bill_pay_overdue');
        $this->dropColumn(Bill::tableName(), 'pay_bill_until');
        $this->dropColumn(Bill::tableName(), 'is_pay_overdue');
    }
}
