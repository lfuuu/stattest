<?php

use app\modules\nnp\models\Operator;

class m161122_125419_add_operator_count extends \app\classes\Migration
{
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $operatorTableName = Operator::tableName();
        $fieldName = 'cnt';
        $this->addColumn($operatorTableName, $fieldName, $this->integer()->notNull()->defaultValue(0));

        // работать с исходной БД
        $this->db = $db;
    }

    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $operatorTableName = Operator::tableName();
        $fieldName = 'cnt';
        $this->dropColumn($operatorTableName, $fieldName);

        // работать с исходной БД
        $this->db = $db;
    }
}