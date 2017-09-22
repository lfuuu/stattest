<?php

use app\models\Country;

/**
 * Class m170921_163619_add_country_is_show
 */
class m170921_163619_add_country_is_show extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Country::tableName(), 'is_show_in_lk', $this->integer()->notNull()->defaultValue(0));
        Country::updateAll(['is_show_in_lk' => 1], ['in_use' => 1]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Country::tableName(), 'is_show_in_lk');
    }
}
