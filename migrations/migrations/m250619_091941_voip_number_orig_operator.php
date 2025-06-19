<?php

/**
 * Class m250619_091941_voip_number_orig_operator
 */
class m250619_091941_voip_number_orig_operator extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Number::tableName(), 'orig_nnp_operator_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Number::tableName(), 'orig_nnp_operator_id');
    }
}
