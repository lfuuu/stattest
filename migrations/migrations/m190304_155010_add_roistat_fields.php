<?php

use app\models\TroubleRoistat;

/**
 * Class m190304_155010_add_roistat_fields
 */
class m190304_155010_add_roistat_fields extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(TroubleRoistat::tableName(), 'roistat_fields', $this->text());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(TroubleRoistat::tableName(), 'roistat_fields');
    }
}
