<?php


use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;

class m160519_122000_create_nnp extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;
//
        $this->createOperator();
        $this->createRegion();
        $this->createNumberRange();

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

        $this->dropNumberRange();
        $this->dropRegion();
        $this->dropOperator();

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Создать NumberRange
     */
    protected function createNumberRange()
    {
        $tableName = NumberRange::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'country_code' => $this->integer(),
            'ndc' => $this->integer(),
            'number_from' => $this->integer(),
            'number_to' => $this->integer(),

            'is_mob' => $this->boolean()->notNull(),
            'is_active' => $this->boolean()->defaultValue(true),

            'operator_source' => $this->string(255),
            'operator_id' => $this->integer(),
            'region_source' => $this->string(255),
            'region_id' => $this->integer(),

            'insert_time' => $this->dateTime(),
            'insert_user_id' => $this->integer(),
            'update_time' => $this->timestamp(),
            'update_user_id' => $this->integer(),
        ]);

        $fieldName = 'operator_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Operator::tableName(), 'id', 'RESTRICT');

        $fieldName = 'region_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Region::tableName(), 'id', 'RESTRICT');

        $this->createIndex('fk-' . str_replace('.', '_', $tableName) . '-' . 'country-ndc-number', $tableName, ['country_code', 'ndc', 'number_from']);
    }

    /**
     * Удалить NumberRange
     */
    protected function dropNumberRange()
    {
        $tableName = NumberRange::tableName();
        $this->dropTable($tableName);
    }

    /**
     * Создать Operator
     */
    protected function createOperator()
    {
        $tableName = Operator::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ]);
    }

    /**
     * Удалить Operator
     */
    protected function dropOperator()
    {
        $tableName = Operator::tableName();
        $this->dropTable($tableName);
    }

    /**
     * Создать Region
     */
    protected function createRegion()
    {
        $tableName = Region::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ]);
    }

    /**
     * Удалить Region
     */
    protected function dropRegion()
    {
        $tableName = Region::tableName();
        $this->dropTable($tableName);
    }
}