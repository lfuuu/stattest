<?php

use app\classes\Migration;
use app\modules\sim\models\Registry;

/**
 * Class m201222_192021_create_region_sim_history
 */
class m201222_192021_create_region_sim_history extends Migration
{
    public $tableName;
    public $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->tableName = Registry::tableName();

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),

            'state' => $this->smallInteger()->notNull(),

            'region_sim_settings_id' => $this->integer(11)->notNull(),

            'count' => $this->integer()->notNull(),

            //9223372036854775807
            'iccid_from' => $this->string(16)->notNull(), // usually 9
            'iccid_to' => $this->string(16)->notNull(),

            'imsi_from' => $this->string(16)->notNull(), // usually 8
            'imsi_to' => $this->string(16)->notNull(),

            'imsi_s1_from' => $this->string(15), // exact 15
            'imsi_s1_to' => $this->string(15),

            'imsi_s2_from' => $this->string(15), // exact 15
            'imsi_s2_to' => $this->string(15),

            'log' => $this->text(),
            'errors' => $this->text(),

            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),

            'started_at' => $this->dateTime(),
            'completed_at' => $this->dateTime(),

            'created_by' => $this->integer(11)->notNull(),

        ], $this->tableOptions);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->tableName = Registry::tableName();

        $this->dropTable($this->tableName);
    }
}
