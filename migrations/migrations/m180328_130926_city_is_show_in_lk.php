<?php

use app\models\City;

/**
 * Class m180328_130926_city_is_show_in_lk
 */
class m180328_130926_city_is_show_in_lk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $table = City::tableName();
        // чтобы значения шли последовательно, сдвигаем старое значение с 1 на 2
        $this->update($table, ['is_show_in_lk' => City::IS_SHOW_IN_LK_FULL], ['is_show_in_lk' => 1]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $table = City::tableName();
        $this->update($table, ['is_show_in_lk' => 1], ['is_show_in_lk' => City::IS_SHOW_IN_LK_FULL]);
    }
}
