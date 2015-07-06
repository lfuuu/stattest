<?php

class m150703_142811_tax_rate extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
          ALTER TABLE `newbill_lines`
              CHANGE COLUMN `tax_type_id` `tax_rate` INT(11) NULL DEFAULT NULL AFTER `is_price_includes_tax`;
        ");

        $this->execute("
          ALTER TABLE `transaction`
              CHANGE COLUMN `tax_type_id` `tax_rate` INT(11) NULL DEFAULT NULL AFTER `amount`;
        ");
    }

    public function down()
    {
        echo "m150703_142811_bill_line_tax_rate cannot be reverted.\n";

        return false;
    }
}