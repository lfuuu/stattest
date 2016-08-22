<?php

use app\modules\nnp\models\PackagePricelist;

class m160822_113058_alter_nnp_pricelist extends \app\classes\Migration
{
    public function up()
    {
        $this->alterPricelist('integer');
    }

    public function down()
    {
        $this->alterPricelist('float');
    }

    /**
     * @param string $fieldType
     */
    public function alterPricelist($fieldType)
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = PackagePricelist::tableName();
        $fieldName = 'pricelist_id';
        $this->alterColumn($tableName, $fieldName, $this->$fieldType());

        // работать с исходной БД
        $this->db = $db;
    }
}