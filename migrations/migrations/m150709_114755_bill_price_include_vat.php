<?php

class m150709_114755_bill_price_include_vat extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `newbills`
            ADD COLUMN `price_include_vat`  tinyint NOT NULL DEFAULT 0 AFTER `is_use_tax`;
        ");
    }

    public function down()
    {
        echo "m150709_114755_bill_price_include_vat cannot be reverted.\n";

        return false;
    }
}