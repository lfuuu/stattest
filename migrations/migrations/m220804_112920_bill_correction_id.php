<?php

/**
 * Class m220804_112920_bill_correction_id
 */
class m220804_112920_bill_correction_id extends \app\classes\Migration
{

    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Bill::tableName(), 'correction_bill_id', $this->integer()->unsigned());

        $this->addForeignKey(\app\models\Bill::tableName().'-correction_bill_id',
            \app\models\Bill::tableName(), 'correction_bill_id',
        \app\models\Bill::tableName(), 'id',
        'RESTRICT', 'RESTRICT');
    }


    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Bill::tableName(), 'correction_bill_id');
    }

}
