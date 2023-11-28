<?php

use app\classes\Migration;
use app\modules\sbisTenzor\models\SBISContractorExchange;

/**
 * Class m231128_133302_sbis_exchange_delete
 */
class m231128_133302_sbis_exchange_delete extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(SBISContractorExchange::tableName(), 'updated_at', $this->dateTime());
        $this->addColumn(SBISContractorExchange::tableName(), 'is_deleted', $this->tinyInteger()->defaultValue(0)->notNull());
        $this->addColumn(SBISContractorExchange::tableName(), 'deleted_at', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(SBISContractorExchange::tableName(), 'updated_at');
        $this->dropColumn(SBISContractorExchange::tableName(), 'is_deleted');
        $this->dropColumn(SBISContractorExchange::tableName(), 'deleted_at');
    }
}
