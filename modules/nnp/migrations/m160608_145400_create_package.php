<?php

use app\modules\nnp\models\Destination;
use app\modules\nnp\models\Package;

class m160608_145400_create_package extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $this->createPackage();

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

        $this->dropPackage();

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Создать Package
     */
    protected function createPackage()
    {
        $tableName = Package::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'tariff_id' => $this->integer()->notNull(),
            'package_type_id' => $this->integer()->notNull(),
            'period_id' => $this->integer()->notNull(),
            'price' => $this->integer(),
            'minute' => $this->integer(),
            'pricelist_id' => $this->integer(),
            'destination_id' => $this->integer()->notNull(),
        ]);

        $fieldName = 'destination_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Destination::tableName(), 'id', 'RESTRICT');

    }

    /**
     * Удалить Package
     */
    protected function dropPackage()
    {
        $tableName = Package::tableName();
        $this->dropTable($tableName);
    }

}