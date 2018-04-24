<?php

use app\models\UsageVoip;

/**
 * Class m180403_074649_usage_voip_number_int
 */
class m180403_074649_usage_voip_number_int extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(UsageVoip::tableName(), 'E164', $this->bigInteger()->notNull()->defaultValue(0));
        $this->dropIndex('E164', UsageVoip::tableName());
        $this->createIndex('E164', UsageVoip::tableName(), 'E164');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(UsageVoip::tableName(), 'E164', "varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT ''");
        $this->dropIndex('E164', UsageVoip::tableName());
        $this->createIndex('E164', UsageVoip::tableName(), 'E164');
    }
}
