<?php

use app\classes\Migration;
use app\modules\uu\models\AccountEntryCorrection;


/**
 * Class m221108_142638_uu_bill_correction
 */
class m221108_142638_uu_bill_correction extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(AccountEntryCorrection::tableName(),
            [
                'client_account_id' => $this->integer()->notNull(),
                'bill_no' =>'varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL',
                'created_at' => $this->dateTime()->notNull(),
                'sum' => $this->decimal(11, 2),
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey(AccountEntryCorrection::tableName() . '-bill_no',
            AccountEntryCorrection::tableName(), 'bill_no',
            \app\models\Bill::tableName(), 'bill_no',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(AccountEntryCorrection::tableName() . '-client_account_id',
            AccountEntryCorrection::tableName(), 'client_account_id',
            \app\models\ClientAccount::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(AccountEntryCorrection::tableName());
    }
}
