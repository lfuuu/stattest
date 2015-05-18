<?php

class m150518_121036_rename_client_contracts extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            RENAME TABLE client_contracts TO client_document;
        ");
    }

    public function down()
    {
        echo "m150518_121036_rename_client_contracts cannot be reverted.\n";

        return false;
    }
}