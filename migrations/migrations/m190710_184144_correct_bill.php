<?php

use app\models\Bill;
use app\models\Invoice;

/**
 * Class m190710_184144_correct_bill
 */
class m190710_184144_correct_bill extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tableName(), 'correction_bill_id', $this->integer(10)->unsigned()->defaultValue(null));
        $this->addColumn(Invoice::tableName(), 'original_sum', $this->decimal(11, 2)->defaultValue(0.0));
        $this->addColumn(Invoice::tableName(), 'original_sum_tax', $this->decimal(11, 2)->defaultValue(0.0));


        $this->addForeignKey(
            'fk-' . Invoice::tableName() . '-correction_bill_id-' . Bill::tableName() . '-id',
            Invoice::tableName(), 'correction_bill_id',
            Bill::tableName(), 'id',
            'SET NULL',
            'SET NULL'
        );

        $this->update(Invoice::tableName(), [
            'original_sum' => new \yii\db\Expression('sum'),
            'original_sum_tax' => new \yii\db\Expression('sum_tax'),
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-' . Invoice::tableName() . '-correction_bill_id-' . Bill::tableName() . '-id',
            Invoice::tableName()
        );
        $this->dropColumn(Invoice::tableName(), 'correction_bill_id');
        $this->dropColumn(Invoice::tableName(), 'original_sum');
        $this->dropColumn(Invoice::tableName(), 'original_sum_tax');
    }
}
