<?php

use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;

class m161011_173800_add_operator_country extends \app\classes\Migration
{
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $fieldName = 'country_prefix';
        $operatorTableName = Operator::tableName();
        $regionTableName = Region::tableName();
        $numberRangeTableName = NumberRange::tableName();
        $this->addColumn($operatorTableName, $fieldName, $this->integer());
        $this->addColumn($regionTableName, $fieldName, $this->integer());

        $sql = <<<SQL
            UPDATE {$operatorTableName}
            SET country_prefix = (SELECT country_prefix FROM {$numberRangeTableName} WHERE operator_id = {$operatorTableName}.id LIMIT 1)
SQL;
        $this->db->createCommand($sql)->execute();

        $sql = <<<SQL
            DELETE FROM {$operatorTableName} WHERE country_prefix IS NULL
SQL;
        $this->db->createCommand($sql)->execute();


        $sql = <<<SQL
            UPDATE {$regionTableName}
            SET country_prefix = (SELECT country_prefix FROM {$numberRangeTableName} WHERE region_id = {$regionTableName}.id LIMIT 1)
SQL;
        $this->db->createCommand($sql)->execute();

        $sql = <<<SQL
            DELETE FROM {$regionTableName} WHERE country_prefix IS NULL
SQL;
        $this->db->createCommand($sql)->execute();

        $this->alterColumn($operatorTableName, $fieldName, 'SET NOT NULL');
        $this->alterColumn($regionTableName, $fieldName, 'SET NOT NULL');

        // работать с исходной БД
        $this->db = $db;
    }

    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $fieldName = 'country_prefix';
        $this->dropColumn(Operator::tableName(), $fieldName);
        $this->dropColumn(Region::tableName(), $fieldName);

        // работать с исходной БД
        $this->db = $db;
    }
}