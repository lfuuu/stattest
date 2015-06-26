<?php

class m150626_170757_firm_account_mcm extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("insert into `firma_pay_account` values('40702810038000034045', 'mcm_telekom')");

    }

    public function down()
    {
        echo "m150626_170757_firm_account_mcm cannot be reverted.\n";

        return false;
    }
}
