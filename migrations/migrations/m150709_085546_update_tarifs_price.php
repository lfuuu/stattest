<?php

class m150709_085546_update_tarifs_price extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE `tarifs_internet` SET
                `pay_once` = ROUND(`pay_once` + (`pay_once` * 0.18), 2),
                `pay_month` = ROUND(`pay_month` + (`pay_month` * 0.18), 2),
                `pay_mb` = ROUND(`pay_mb` + (`pay_mb` * 0.18), 2);
        ");

        $this->execute("
            UPDATE `tarifs_extra` SET
                `price` = ROUND(`price` + (`price` * 0.18), 2);
        ");

        $this->execute("
            UPDATE `tarifs_virtpbx` SET
                `price` = ROUND(`price` + (`price` * 0.18), 2),
                `overrun_per_port` = ROUND(`overrun_per_port` + (`overrun_per_port` * 0.18), 2),
                `overrun_per_gb` = ROUND(`overrun_per_gb` + (`overrun_per_gb` * 0.18), 2);
        ");

        $this->execute("
            UPDATE `tarifs_sms` SET
                `per_month_price` = ROUND(`per_month_price` + (`per_month_price` * 0.18), 2);
        ");

        $this->execute("
            UPDATE `tarifs_voip` SET
                `month_line` = ROUND(`month_line` + (`month_line` * 0.18), 2),
                `month_number` = ROUND(`month_number` + (`month_number` * 0.18), 2),
                `month_min_payment` = ROUND(`month_min_payment` + (`month_min_payment` * 0.18), 2),
                `once_line` = ROUND(`once_line` + (`once_line` * 0.18), 2),
                `once_number` = ROUND(`once_number` + (`once_number` * 0.18), 2)
            WHERE `status` != 'operator';
        ");
    }

    public function down()
    {
        echo "m150709_085546_update_tarifs_price cannot be reverted.\n";

        return false;
    }
}