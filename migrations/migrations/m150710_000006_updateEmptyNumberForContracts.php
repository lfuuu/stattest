<?php

class m150710_000006_updateEmptyNumberForContracts extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE client_contract cc
                INNER JOIN clients c ON cc.id = c.`contract_id`
                SET cc.`number` = c.`id`
                WHERE cc.number = ''
	    ");
    }

    public function down()
    {
        echo "m150629_191154_updateEmptyNumberForContracts cannot be reverted.\n";

        return false;
    }
}