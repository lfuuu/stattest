<?php

class m160201_134838_usage_voip_pack__status extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(
            "usage_voip_package", 
            "status", 
            "enum('connecting','working') NOT NULL DEFAULT 'working'"
        );
    }

    public function down()
    {
        echo "m160201_134838_usage_voip_pack__status cannot be reverted.\n";

        return false;
    }
}
