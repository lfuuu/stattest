<?php

use app\modules\nnp\models\NumberRange;

/**
 */
class m161012_190600_add_number_range_date extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();
        $this->addColumn($tableName, 'date_stop', $this->date());
        $this->addColumn($tableName, 'date_resolution', $this->date());
        $this->addColumn($tableName, 'detail_resolution', $this->string(255));
        $this->addColumn($tableName, 'status_number', $this->string(255));

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = NumberRange::tableName();
        $this->dropColumn($tableName, 'date_stop');
        $this->dropColumn($tableName, 'date_resolution');
        $this->dropColumn($tableName, 'detail_resolution');
        $this->dropColumn($tableName, 'status_number');

        // работать с исходной БД
        $this->db = $db;
    }
}
