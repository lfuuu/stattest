<?php

use app\models\Number;

class m160420_101151_7800_cost extends \app\classes\Migration
{
    public function up()
    {
        $this->update(Number::tableName(), ['price' => 1200], ['operator_account_id' => 38319]);
    }

    public function down()
    {
        $this->update(Number::tableName(), ['price' => 0], ['operator_account_id' => 38319]);
    }
}