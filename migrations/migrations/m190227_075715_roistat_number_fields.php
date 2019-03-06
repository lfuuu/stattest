<?php

use app\models\RoistatNumberFields;

/**
 * Class m190227_075715_roistat_number_fields
 */
class m190227_075715_roistat_number_fields extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(RoistatNumberFields::tableName(), [
            'id' => $this->primaryKey(),
            'number' => $this->bigInteger()->notNull(),
            'fields' => $this->text()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(RoistatNumberFields::tableName());
    }
}
