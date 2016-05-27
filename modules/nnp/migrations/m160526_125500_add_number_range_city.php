<?php


use app\modules\nnp\models\NumberRange;

class m160526_125500_add_number_range_city extends \app\classes\Migration
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
        $this->addNumberRangeCity();

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

        $this->dropNumberRangeCity();

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Добавить NumberRange.City
     */
    protected function addNumberRangeCity()
    {
        $tableName = NumberRange::tableName();
        $this->addColumn($tableName, 'city_id', $this->integer()); // FK нет, потому что таблица городов в другой БД
    }

    /**
     * Удалить NumberRange.City
     */
    protected function dropNumberRangeCity()
    {
        $tableName = NumberRange::tableName();
        $this->dropColumn($tableName, 'city_id');
    }
}