<?php

use app\models\Number;
use app\models\TariffNumber;

class m160518_105558_voip_numbers_drop_price extends \app\classes\Migration
{
    public function up()
    {
        $this->dropColumn(Number::tableName(), 'price');
    }

    public function down()
    {
        $this->addColumn(Number::tableName(), 'price', $this->integer(11)->defaultValue(0));
        $this->execute('
            UPDATE
                ' . Number::tableName() . ' vn
                LEFT JOIN ' . TariffNumber::tableName() . ' tn
                    ON tn.`city_id` = vn.`city_id` AND tn.`did_group_id` = vn.`did_group_id`
            SET
                vn.`price` = tn.`activation_fee`
        ');
    }
}