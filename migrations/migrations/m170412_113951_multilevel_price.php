<?php
use app\models\ClientAccount;
use app\models\DidGroup;

/**
 * Class m170412_113951_multilevel_price
 */
class m170412_113951_multilevel_price extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientAccount::tableName(), 'price_level', $this->integer()->notNull()->defaultValue(ClientAccount::DEFAULT_PRICE_LEVEL));

        for ($i = 4; $i <= 9; $i++) {
            $this->addColumn(DidGroup::tableName(), 'price' . $i, $this->integer());
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientAccount::tableName(), 'price_level');

        for ($i = 4; $i <= 9; $i++) {
            $this->dropColumn(DidGroup::tableName(), 'price' . $i);
        }
    }
}
