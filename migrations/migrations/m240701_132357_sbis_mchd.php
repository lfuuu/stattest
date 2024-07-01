<?php

use app\classes\Migration;
use app\modules\sbisTenzor\models\SBISMchd;

/**
 * Class m240701_132357_sbis_mchd
 */
class m240701_132357_sbis_mchd extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(SBISMchd::tableName(), [
            'id' => $this->primaryKey(),
            'mchd_number' => $this->string(256),
            'mchd_xml' => $this->text(),
            'sbis_organization_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->null(),
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(SBISMchd::tableName());
    }
}
