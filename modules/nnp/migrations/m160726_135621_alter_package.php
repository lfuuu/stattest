<?php

use app\modules\nnp\models\Destination;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;

/**
 * Handles the altering for table `package`.
 */
class m160726_135621_alter_package extends \app\classes\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        // работать с PostgreSql NNP
        $db = $this->db;
        $this->db = Yii::$app->dbPgNnp;

        $tableName = Package::tableName();
        $this->dropColumn($tableName, 'period_id');
        $this->dropColumn($tableName, 'name');
        $this->dropColumn($tableName, 'id');
        $this->addPrimaryKey('package_pkey', $tableName, 'tariff_id');

        $this->createPackageMinute();
        $this->createPackagePrice();
        $this->createPackagePricelist();

        $this->dropColumn($tableName, 'package_type_id');
        $this->dropColumn($tableName, 'price');
        $this->dropColumn($tableName, 'minute');
        $this->dropColumn($tableName, 'pricelist_id');
        $this->dropColumn($tableName, 'destination_id');

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

        $tableName = Package::tableName();

        $this->addColumn($tableName, 'package_type_id', $this->integer()->notNull());
        $this->addColumn($tableName, 'price', $this->float());
        $this->addColumn($tableName, 'minute', $this->integer());
        $this->addColumn($tableName, 'pricelist_id', $this->integer());
        $this->addColumn($tableName, 'destination_id', $this->integer());

        $this->dropPackageMinute();
        $this->dropPackagePrice();
        $this->dropPackagePricelist();

        $this->dropPrimaryKey('package_pkey', $tableName);
        $this->addColumn($tableName, 'id', $this->primaryKey());
        $this->addColumn($tableName, 'name', $this->string(255));
        $this->addColumn($tableName, 'period_id', $this->integer());

        // работать с исходной БД
        $this->db = $db;
    }

    /**
     * Создать PackageMinute
     */
    protected function createPackageMinute()
    {
        $tableName = PackageMinute::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'destination_id' => $this->integer()->notNull(),
            'minute' => $this->integer()->notNull(),
        ]);

        $fieldName = 'tariff_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Package::tableName(), 'tariff_id', 'CASCADE');

        $fieldName = 'destination_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Destination::tableName(), 'id', 'RESTRICT');

        $packageTableName = Package::tableName();
        $this->execute("
            INSERT INTO {$tableName}
                (tariff_id, destination_id, minute)
            SELECT tariff_id, destination_id, minute
            FROM {$packageTableName}
            WHERE package_type_id = 1
        ");
    }

    /**
     * Удалить PackageMinute
     */
    protected function dropPackageMinute()
    {
        $tableName = PackageMinute::tableName();
        $this->dropTable($tableName);
    }

    /**
     * Создать PackagePrice
     */
    protected function createPackagePrice()
    {
        $tableName = PackagePrice::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'destination_id' => $this->integer()->notNull(),
            'price' => $this->float()->notNull(),
        ]);

        $fieldName = 'tariff_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Package::tableName(), 'tariff_id', 'CASCADE');

        $fieldName = 'destination_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Destination::tableName(), 'id', 'RESTRICT');

        $packageTableName = Package::tableName();
        $this->execute("
            INSERT INTO {$tableName}
                (tariff_id, destination_id, price)
            SELECT tariff_id, destination_id, price
            FROM {$packageTableName}
            WHERE package_type_id = 2
        ");
    }

    /**
     * Удалить PackagePrice
     */
    protected function dropPackagePrice()
    {
        $tableName = PackagePrice::tableName();
        $this->dropTable($tableName);
    }

    /**
     * Создать PackagePricelist
     */
    protected function createPackagePricelist()
    {
        $tableName = PackagePricelist::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'pricelist_id' => $this->float()->notNull(),
        ]);

        $fieldName = 'tariff_id';
        $this->addForeignKey('fk-' . str_replace('.', '_', $tableName) . '-' . $fieldName, $tableName, $fieldName, Package::tableName(), 'tariff_id', 'CASCADE');

        $packageTableName = Package::tableName();
        $this->execute("
            INSERT INTO {$tableName}
                (tariff_id, pricelist_id)
            SELECT tariff_id, pricelist_id
            FROM {$packageTableName}
            WHERE package_type_id = 3
        ");
    }

    /**
     * Удалить PackagePricelist
     */
    protected function dropPackagePricelist()
    {
        $tableName = PackagePricelist::tableName();
        $this->dropTable($tableName);
    }
}
