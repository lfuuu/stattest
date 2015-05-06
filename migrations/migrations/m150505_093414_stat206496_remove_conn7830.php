<?php

class m150505_093414_stat206496_remove_conn7830 extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
                DELETE FROM usage_ip_ports WHERE id=7830;
        ");
    }

    public function down()
    {
        echo "m150505_093414_stat206496_remove_conn7830 cannot be reverted.\n";

        return false;
    }
}
