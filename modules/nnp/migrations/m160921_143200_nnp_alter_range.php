<?php

use app\models\Country;
use app\modules\nnp\models\NumberRange;
use yii\db\Expression;

class m160921_143200_nnp_alter_range extends \app\classes\Migration
{
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();
        $this->dropIndex('nnp.fk-' . str_replace('.', '_', $tableName) . '-' . 'country-ndc-number', $tableName);
        $this->createIndex('idx-' . str_replace('.', '_', $tableName) . '-' . 'full-number', $tableName, ['full_number_from']);

        // работать с исходной БД
        $this->db = $db;
    }

    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();
        $this->dropIndex('nnp.idx-' . str_replace('.', '_', $tableName) . '-' . 'full-number', $tableName);
        $this->createIndex('fk-' . str_replace('.', '_', $tableName) . '-' . 'country-ndc-number', $tableName, ['country_code', 'ndc', 'number_from']);

        // работать с исходной БД
        $this->db = $db;
    }
}