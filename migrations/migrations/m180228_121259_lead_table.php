<?php

use app\models\ClientAccount;
use app\models\Lead;
use app\models\Trouble;
use app\models\TroubleState;

/**
 * Class m180228_121259_lead_table
 */
class m180228_121259_lead_table extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(Lead::tableName(), [
            'id' => $this->primaryKey(),
            'message_id' => $this->string(),
            'trouble_id' => $this->integer(),
            'account_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'data_json' => $this->text(),
            'state_id' => $this->integer()->unsigned()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        $this->createIndex('idx_message_id', Lead::tableName(), ['message_id']);
        $this->addForeignKey('fk-' . Lead::tableName() . '-account_id-' . ClientAccount::tableName() . '-id', Lead::tableName(), 'account_id', ClientAccount::tableName(), 'id');
        $this->addForeignKey('fk-' . Lead::tableName() . '-trouble_id-' . Trouble::tableName() . '-id', Lead::tableName(), 'trouble_id', Trouble::tableName(), 'id');
        $this->addForeignKey('fk-' . Lead::tableName() . '-state_id-' . TroubleState::tableName() . '-id', Lead::tableName(), 'state_id', TroubleState::tableName(), 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Lead::tableName());
    }
}
