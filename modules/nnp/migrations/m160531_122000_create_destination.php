<?php

use app\modules\nnp\models\Destination;
use app\modules\nnp\models\Prefix;
use app\modules\nnp\models\PrefixDestination;

class m160531_122000_create_destination extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $this->createDestination();
        $this->createPrefixDestination();

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

        $this->dropPrefixDestination();
        $this->dropDestination();

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Создать Destination
     */
    protected function createDestination()
    {
        $tableName = Destination::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ]);
    }

    /**
     * Удалить Destination
     */
    protected function dropDestination()
    {
        $tableName = Destination::tableName();
        $this->dropTable($tableName);
    }

    /**
     * Создать PrefixDestination
     */
    protected function createPrefixDestination()
    {
        $tableName = PrefixDestination::tableName();
        $this->createTable($tableName, [
            'prefix_id' => $this->integer(),
            'destination_id' => $this->integer(),
            'is_addition' => $this->boolean(),

            'insert_time' => $this->dateTime(),
            'insert_user_id' => $this->integer(),
        ]);

        $this->addPrimaryKey('prefix_destination_pkey', $tableName, ['destination_id', 'prefix_id']);

        $fieldName = 'prefix_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Prefix::tableName(), 'id', 'CASCADE');

        $fieldName = 'destination_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Destination::tableName(), 'id', 'CASCADE');

    }

    /**
     * Удалить PrefixDestination
     */
    protected function dropPrefixDestination()
    {
        $tableName = PrefixDestination::tableName();
        $this->dropTable($tableName);
    }

}