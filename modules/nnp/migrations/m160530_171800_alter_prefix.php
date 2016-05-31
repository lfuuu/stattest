<?php

use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\models\Prefix;

class m160530_171800_alter_prefix extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $this->alterNumberRangePrefix('CASCADE');

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

        $this->alterNumberRangePrefix('RESTRICT');

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Изменить NumberRangePrefix
     */
    protected function alterNumberRangePrefix($fkType)
    {
        $tableName = NumberRangePrefix::tableName();

        $fieldName = 'number_range_id';
        $this->dropForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName);
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, NumberRange::tableName(), 'id', $fkType);

        $fieldName = 'prefix_id';
        $this->dropForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName);
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Prefix::tableName(), 'id', $fkType);

    }
}