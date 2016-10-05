<?php

use app\modules\nnp\models\AccountTariffLight;

class m161005_095032_add_account_tariff_second extends \app\classes\Migration
{
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = AccountTariffLight::tableName();
        $this->addColumn($tableName, 'tariffication_by_minutes', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn($tableName, 'tariffication_full_first_minute', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn($tableName, 'tariffication_free_first_seconds', $this->boolean()->notNull()->defaultValue(true));

        // работать с исходной БД
        $this->db = $db;
    }

    public function down()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = AccountTariffLight::tableName();
        $this->dropColumn($tableName, 'tariffication_by_minutes');
        $this->dropColumn($tableName, 'tariffication_full_first_minute');
        $this->dropColumn($tableName, 'tariffication_free_first_seconds');

        // работать с исходной БД
        $this->db = $db;
    }
}