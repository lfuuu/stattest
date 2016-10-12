<?php

use app\modules\nnp\models\AccountTariffLight;

/**
 */
class m161011_190600_add_package_price extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = AccountTariffLight::tableName();
        $this->addColumn($tableName, 'price', $this->float());

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

        $tableName = AccountTariffLight::tableName();
        $this->dropColumn($tableName, 'price');

        // работать с исходной БД
        $this->db = $db;
    }
}
