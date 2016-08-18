<?php

use app\modules\nnp\models\AccountTariffLight;
use app\modules\nnp\models\Package;

/**
 */
class m160816_144100_create_account_tariff_light extends \app\classes\Migration
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
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'number' => $this->bigInteger()->notNull(),
            'account_client_id' => $this->integer()->notNull(),
            'tariff_id' => $this->integer()->notNull(),
            'activate_from' => $this->date()->notNull(),
            'deactivate_from' => $this->date(),
        ]);

        $fieldName = 'tariff_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Package::tableName(), 'tariff_id', 'CASCADE');

        $this->createIndex('idx-' . str_replace('.', '_', $tableName) . '-account_client_id', $tableName, ['account_client_id']);

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
        $this->dropTable($tableName);

        // работать с исходной БД
        $this->db = $db;
    }
}
