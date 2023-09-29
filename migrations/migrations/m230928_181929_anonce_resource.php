<?php

use app\classes\Migration;
use app\modules\uu\models\AccountTariffResourceLog;

/**
 * Class m230928_181929_anonce_resource
 */
class m230928_181929_anonce_resource extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariffResourceLog::tableName(), 'is_announced', $this->tinyInteger()->notNull()->defaultValue(1));
        $this->createIndex('idx-announce-actual_from_utc', AccountTariffResourceLog::tableName(), ['is_announced', 'actual_from_utc']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('idx-announce-actual_from_utc', AccountTariffResourceLog::tableName());
        $this->dropColumn(AccountTariffResourceLog::tableName(), 'is_announced');
    }
}
