<?php

class m160229_151502_product_state__feedback extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn('product_state', 'product', 'enum(\'vpbx\',\'phone\',\'feedback\') NOT NULL DEFAULT \'phone\'');
    }

    public function down()
    {
        $this->delete('product_state', ['product' => 'feedback']);
        $this->alterColumn('product_state', 'product', 'enum(\'vpbx\',\'phone\') NOT NULL DEFAULT \'phone\'');
    }
}