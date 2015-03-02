<?php

class m150302_132848_update_contract_type extends \app\classes\Migration
{
    public function up()
    {
        
        $this->execute("
           UPDATE `client_contract_type` SET `id`='3',`name`='Межоператорка',`sort`='3' WHERE `id`='3';
        ");
        //удаление типа договора "входящие"
        $this->execute("
           delete from `client_contract_type` WHERE `id`='1';
        ");
        

    }

    public function down()
    {
        echo "m150302_132848_update_contract_type cannot be reverted.\n";

        return false;
    }
}