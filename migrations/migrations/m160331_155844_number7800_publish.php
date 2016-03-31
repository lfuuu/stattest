<?php

use \app\models\Number;

class m160331_155844_number7800_publish extends \app\classes\Migration
{
    public function up()
    {
        $this->update(Number::tableName(), ['status' => Number::STATUS_INSTOCK], ['number_type' => 5]);
    }

    public function down()
    {
        $this->update(Number::tableName(), ['status' => Number::STATUS_NOTSELL], ['number_type' => 5]);
    }
}