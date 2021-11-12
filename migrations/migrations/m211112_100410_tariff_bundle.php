<?php

use app\classes\Migration;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffBundle;

/**
 * Class m211112_100410_tariff_bundle
 */
class m211112_100410_tariff_bundle extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Tariff::tableName(), 'is_bundle', $this->integer()->notNull()->defaultValue(0));
        $this->createTable(TariffBundle::tableName(), [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'package_tariff_id' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('fk-'.TariffBundle::tableName().'-tariff',
            TariffBundle::tableName(), 'tariff_id',
            Tariff::tableName(), 'id'
        );

        $this->addForeignKey('fk-'.TariffBundle::tableName().'-package_tariff_id',
            TariffBundle::tableName(), 'package_tariff_id',
            Tariff::tableName(), 'id'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Tariff::tableName(), 'is_bundle');
        $this->dropTable(TariffBundle::tableName());
    }
}
