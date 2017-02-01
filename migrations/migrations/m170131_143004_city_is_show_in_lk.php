<?php

/**
 * Class m170131_143004_city_is_show_in_lk
 */
class m170131_143004_city_is_show_in_lk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\City::tableName(), 'is_show_in_lk', $this->smallInteger()->notNull()->defaultValue(0));
        $this->update(\app\models\City::tableName(), ['is_show_in_lk' => 1], ['in_use' => 1]);
        $this->createIndex('idx-is_show_in_lk', \app\models\City::tableName(), 'is_show_in_lk');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\City::tableName(), 'is_show_in_lk');
    }
}
