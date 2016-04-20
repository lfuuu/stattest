<?php

class m160411_145552_site_name extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn("clients", "site_name", $this->string(128)->notNull()->defaultValue(''));
    }

    public function down()
    {
        $this->dropColumn("clients", "site_name");
    }
}