<?php

use app\modules\nnp\models\PrefixDestination;

class m160601_173500_alter_destination extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = PrefixDestination::tableName();
        $this->dropPrimaryKey('prefix_destination_pkey', $tableName);
        $this->addPrimaryKey('prefix_destination_pkey', $tableName, ['destination_id', 'prefix_id', 'is_addition']);

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = PrefixDestination::tableName();
        $this->dropPrimaryKey('prefix_destination_pkey', $tableName);
        $this->addPrimaryKey('prefix_destination_pkey', $tableName, ['destination_id', 'prefix_id']);

        // работать с исходной БД
        $this->db = $db;
    }
}