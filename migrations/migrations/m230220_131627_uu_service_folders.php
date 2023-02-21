<?php

use app\classes\Migration;
use app\models\PriceLevel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\ServiceTypeFolder;
use app\modules\uu\models\TariffStatus;

/**
 * Class m230220_131627_uu_service_folders
 */
class m230220_131627_uu_service_folders extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(ServiceTypeFolder::tableName(), [
            'id' => $this->primaryKey(),
            'service_type_id' => $this->integer()->notNull(),
            'price_level_id' => $this->integer()->notNull(),
            'tariff_status_main_id' => $this->integer()->notNull(),
            'tariff_status_package_id' => $this->integer(),
        ]);

        $this->addForeignKey('fk-' . ServiceTypeFolder::tableName() . '-service_type_id',
            ServiceTypeFolder::tableName(), 'service_type_id',
            ServiceType::tableName(), 'id',
        );

        $this->addForeignKey('fk-' . ServiceTypeFolder::tableName() . '-price_level_id',
            ServiceTypeFolder::tableName(), 'price_level_id',
            PriceLevel::tableName(), 'id',
        );

        $this->addForeignKey('fk-' . ServiceTypeFolder::tableName() . '-tariff_status_main_id',
            ServiceTypeFolder::tableName(), 'tariff_status_main_id',
            TariffStatus::tableName(), 'id',
        );

        $this->addForeignKey('fk-' . ServiceTypeFolder::tableName() . '-tariff_status_package_id',
            ServiceTypeFolder::tableName(), 'tariff_status_package_id',
            TariffStatus::tableName(), 'id',
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(ServiceTypeFolder::tableName());
    }
}
