<?php

use app\models\Country;
use app\modules\nnp\models\NumberRange;

class m160920_143200_nnp_alter_range extends \app\classes\Migration
{
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();
        $this->addColumn($tableName, 'country_prefix', $this->integer()->notNull()->defaultValue(Country::PREFIX_RUSSIA));
        $this->alterColumn($tableName, 'country_prefix', 'DROP DEFAULT');

        $this->dropIndex('nnp.fk-' . str_replace('.', '_', $tableName) . '-' . 'country-ndc-number', $tableName);
        $this->createIndex('fk-' . str_replace('.', '_', $tableName) . '-' . 'country-ndc-number', $tableName, ['country_prefix', 'ndc', 'number_from']);

        // работать с исходной БД
        $this->db = $db;
    }

    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();

        $this->dropIndex('nnp.fk-' . str_replace('.', '_', $tableName) . '-' . 'country-ndc-number', $tableName);
        $this->createIndex('fk-' . str_replace('.', '_', $tableName) . '-' . 'country-ndc-number', $tableName, ['country_code', 'ndc', 'number_from']);

        $this->dropColumn($tableName, 'country_prefix');

        // работать с исходной БД
        $this->db = $db;
    }
}