<?php

use app\models\ClientAccount;
use app\models\PriceLevel;
/**
 * Class m210305_110345_clients_price_level
 */
class m210305_110345_clients_price_level extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(PriceLevel::tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string(25)->notNull(),
            'order' => $this->integer(),
        ]);

        $priceLevelList = ClientAccount::getPriceLevels();
        $i = 1;
        foreach ($priceLevelList as $index => $priceLevel) {
            $this->insert(PriceLevel::tableName(), [
                'id' => $index,
                'name' => $priceLevel,
                'order' => $i,
            ]);
            $i++;
        }

        $this->createIndex(
            'idx-' . PriceLevel::tableName() . '-id',
            PriceLevel::tableName(),
            'id',
        );

        $this->addForeignKey(
            'fk-' . ClientAccount::tableName() . '-price_level',
            ClientAccount::tableName(),
            'price_level',
            PriceLevel::tableName(),
            'id',

        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PriceLevel::tableName());
    }
}