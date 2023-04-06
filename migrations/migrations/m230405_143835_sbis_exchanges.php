<?php

use app\classes\Migration;
use app\modules\sbisTenzor\models\SBISContractorExchange;

/**
 * Class m230405_143835_sbis_exchanges
 */
class m230405_143835_sbis_exchanges extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(SBISContractorExchange::tableName(), [
            'id' => $this->primaryKey(),
            'contractor_id' => $this->integer()->notNull(),
            'exchange_id' => $this->string(64)->notNull(),
            'operator_name' => $this->string(64)->notNull(),
            'is_main' => $this->tinyInteger()->notNull()->defaultValue(0),
            'is_roaming' => $this->tinyInteger()->notNull()->defaultValue(0),
            'exchange_state_code' => $this->tinyInteger()->notNull()->defaultValue(0),
            'exchange_state_code_description' => $this->string(32)->notNull()->defaultValue(''),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-' . SBISContractorExchange::tableName() . '-contractor_id--' . \app\modules\sbisTenzor\models\SBISContractor::tableName() . '-id',
            SBISContractorExchange::tableName(), 'contractor_id',
            \app\modules\sbisTenzor\models\SBISContractor::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(SBISContractorExchange::tableName());
    }
}
