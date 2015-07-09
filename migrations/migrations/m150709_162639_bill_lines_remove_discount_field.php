<?php

class m150709_162639_bill_lines_remove_discount_field extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `newbill_lines`
            DROP COLUMN `discount`;
        ");

        $this->execute("
            update newbill_lines
            set discount_auto = round(discount_auto*100/118, 4), discount_set = round(discount_set*100/118, 4)
            where discount_auto != 0 or discount_set != 0;
        ");
    }

    public function down()
    {
        echo "m150709_162639_bill_lines_remove_discount_field cannot be reverted.\n";

        return false;
    }
}