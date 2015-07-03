<?php

class m150703_105126_public74996854 extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("update `voip_numbers` set client_id = null where number like '74996854%' and usage_id is null");
    }

    public function down()
    {
        echo "m150703_105126_public74996854 cannot be reverted.\n";

        return false;
    }
}
