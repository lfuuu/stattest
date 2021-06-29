<?php

use app\models\DidGroup;
use app\models\DidGroupPriceLevel;
use app\models\PriceLevel;
use app\modules\uu\models\TariffStatus;

/**
 * Class m210405_101538_did_group_price_level
 */
class m210405_101538_did_group_price_level extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(DidGroupPriceLevel::tableName(), [
            'id' => $this->primaryKey(),
            'did_group_id' => $this->integer()->notNull(),
            'price_level_id' => $this->integer(),
            'price' => $this->integer(),
            'tariff_status_main_id' => $this->integer(),
            'tariff_status_package_id' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-' . DidGroupPriceLevel::tableName() . '-price_level',
            DidGroupPriceLevel::tableName(),
            'price_level_id',
            PriceLevel::tableName(),
            'id',
        );

        $this->addForeignKey(
            'fk-' . DidGroupPriceLevel::tableName() . '-did_group',
            DidGroupPriceLevel::tableName(),
            'did_group_id',
            DidGroup::tableName(),
            'id',
        );

        $this->addForeignKey(
            'fk-' . DidGroupPriceLevel::tableName() . '-tariff_status_package',
            DidGroupPriceLevel::tableName(),
            'tariff_status_package_id',
            TariffStatus::tableName(),
            'id',
        );

        $this->addForeignKey(
            'fk-' . DidGroupPriceLevel::tableName() . '-tariff_status_main',
            DidGroupPriceLevel::tableName(),
            'tariff_status_main_id',
            TariffStatus::tableName(),
            'id',
        );

        $groups = DidGroup::find()->asArray()->all();
        $priceLevels = PriceLevel::find()->asArray()->all();
        $j = 1;
        foreach ($groups as $group) {
            foreach ($priceLevels as $index => $priceLevel) {
                if ($group['price' . $priceLevel['id']] !== null) {
                    $this->insert(DidGroupPriceLevel::tableName(), [
                        'id' => $j,
                        'did_group_id' => $group['id'],
                        'price_level_id' => $priceLevel['id'],
                        'price' => $group['price' . $priceLevel['id']],
                        'tariff_status_main_id' => $group['tariff_status_main' . $priceLevel['id']],
                        'tariff_status_package_id' => $group['tariff_status_package' . $priceLevel['id']],
                    ]);
                    $j++;
                }
            }
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(DidGroupPriceLevel::tableName());
    }
}
