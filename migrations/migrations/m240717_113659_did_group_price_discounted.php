<?php

/**
 * Class m240717_113659_did_group_price_discounted
 */
class m240717_113659_did_group_price_discounted extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\DidGroupPriceLevel::tableName(), 'price_discounted', $this->integer());
        $this->addColumn(\app\models\Number::tableName(), 'is_with_discount', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\DidGroupPriceLevel::tableName(), 'price_discounted');
        $this->dropColumn(\app\models\Number::tableName(), 'is_with_discount');
    }
}
