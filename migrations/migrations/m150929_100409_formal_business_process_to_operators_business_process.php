<?php

class m150929_100409_formal_business_process_to_operators_business_process extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            INSERT INTO `client_contract_business_process_status`
            SET
                `business_process_id` = 11,
                `name` = 'Формальные',
                `sort` = 4;
        ");

        $this->execute("
            UPDATE `client_contract`
            SET
                `business_process_id` = 11,
                `business_process_status_id` = 125
            WHERE
                `business_process_id` = 14;
        ");

        $this->execute("
            DELETE FROM `client_contract_business_process` WHERE `id` = 14;
        ");
    }

    public function down()
    {
        echo "m150929_100409_formal_business_process_to_operators_business_process cannot be reverted.\n";

        return false;
    }
}