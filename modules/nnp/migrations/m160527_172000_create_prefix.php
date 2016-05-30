<?php

use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\models\Prefix;

class m160527_172000_create_prefix extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $this->createPrefix();
        $this->createNumberRangePrefix();

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

        $this->dropNumberRangePrefix();
        $this->dropPrefix();

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Создать Prefix
     */
    protected function createPrefix()
    {
        $tableName = Prefix::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ]);
    }

    /**
     * Удалить Prefix
     */
    protected function dropPrefix()
    {
        $tableName = Prefix::tableName();
        $this->dropTable($tableName);
    }

    /**
     * Создать NumberRangePrefix
     */
    protected function createNumberRangePrefix()
    {
        $tableName = NumberRangePrefix::tableName();
        $this->createTable($tableName, [
            'prefix_id' => $this->integer(),
            'number_range_id' => $this->integer(),

            'insert_time' => $this->dateTime(),
            'insert_user_id' => $this->integer(),
        ]);

        $this->addPrimaryKey('number_range_prefix_pkey', $tableName, ['prefix_id', 'number_range_id']);

        $fieldName = 'number_range_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, NumberRange::tableName(), 'id', 'RESTRICT');

        $fieldName = 'prefix_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Prefix::tableName(), 'id', 'RESTRICT');

    }

    /**
     * Удалить NumberRangePrefix
     */
    protected function dropNumberRangePrefix()
    {
        $tableName = NumberRangePrefix::tableName();
        $this->dropTable($tableName);
    }

}