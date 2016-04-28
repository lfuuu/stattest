<?php

use app\models\DidGroup;

class m160428_142134_did_group_83 extends \app\classes\Migration
{
    public function up()
    {
        $this->batchInsert(DidGroup::tableName(),['id','name','city_id','beauty_level'],
            [
                [null, 'Стандартные', 74212, 0],
                [null, 'Платиновые', 74212, 1],
                [null, 'Золотые', 74212, 2],
                [null, 'Серебряные', 74212, 3],
                [null, 'Бронзовые', 74212, 4]
            ]
        );
    }

    public function down()
    {
        $this->delete(DidGroup::tableName(), ['city_id' => 74212]);
    }
}