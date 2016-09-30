<?php

use app\modules\nnp\models\NumberRange;
use yii\db\DataReader;

class m160930_094922_fill_nnp_country extends \app\classes\Migration
{
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();
        $this->alterColumn($tableName, 'ndc', 'DROP NOT NULL');
        $this->alterColumn($tableName, 'is_mob', 'DROP NOT NULL');

        $sql = 'SELECT unnest(prefix) as prefix, name FROM geo.geo WHERE fo IS NULL AND region IS NULL';
        /** @var DataReader $dataReader */
        $dataReader = $this->db->createCommand($sql)->query();
        foreach ($dataReader as $row) {
            $prefix = $row['prefix'];
            $name = $row['name'];
            for ($i = 3; $i <= 15 - strlen($prefix); $i++) { // минимальный номер - 3 символа (не считая префикса страны), максимальный - 15 (включая префикс)
                $numberRange = new NumberRange;
                $numberRange->country_prefix = $prefix;
                $numberRange->number_from = str_repeat('0', min($i, 9));
                $numberRange->number_to = str_repeat('9', min($i, 9));
                $numberRange->full_number_from = $numberRange->country_prefix . $numberRange->ndc . str_repeat('0', $i);
                $numberRange->full_number_to = $numberRange->country_prefix . $numberRange->ndc . str_repeat('9', $i);
                $numberRange->region_source = $name;
                $numberRange->save();
            }
        }

        // работать с исходной БД
        $this->db = $db;
    }

    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();
        $this->alterColumn($tableName, 'ndc', 'SET NOT NULL');
        $this->alterColumn($tableName, 'is_mob', 'SET NOT NULL');

        // работать с исходной БД
        $this->db = $db;
    }
}