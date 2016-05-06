<?php

class m160303_134128_number_short_field extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn('voip_numbers', 'number', 'varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');

        $this->batchInsert('did_group', ['id', 'name', 'city_id', 'beauty_level'], [
            [null, 'Стандартные', 49, 0],
            [null, 'Платиновые', 49, 1],
            [null, 'Золотые', 49, 2],
            [null, 'Серебряные', 49, 3],
            [null, 'Бронзовые', 49, 4]
        ]);
    }

    public function down()
    {
        $this->delete('did_group', ['city_id' => 49]);
        $this->delete('voip_numbers', ['region' => 82]);
        $this->alterColumn('voip_numbers', 'number', 'varchar(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL');
    }
}
