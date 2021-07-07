<?php
use app\models\BillOutcomeCorrection;
/**
 * Class m210519_141256_create_newbills_outcome_corrections
 */
class m210519_141256_create_newbills_outcome_corrections extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(BillOutcomeCorrection::tableName(), [
            'id' => $this->primaryKey(),
            'bill_no' => $this->string()->notNull(),
            'original_bill_no' => $this->string()->notNull() ,
            'date_created' => $this->dateTime()->notNull(),
        ]);

        $this->addColumn(BillOutcomeCorrection::tableName(), 'correction_number', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(BillOutcomeCorrection::tableName());
    }
}
