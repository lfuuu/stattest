<?php

class m150930_101610_onlime_devices_folder extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE `tt_states`
            SET
                `name` = 'OnLime Оборудование',
                `state_1c` = 'КОтгрузке'
            WHERE
                `id` = 32;
        ");

        $this->execute("
            UPDATE `tt_folders`
            SET
                `name` = 'OnLime Оборудование'
            WHERE
                `pk` = (SELECT `pk` FROM `tt_states` WHERE `id` = 32)
        ");

        $this->execute("
            UPDATE `tt_states`
            SET
                `name` = 'Отложенные'
            WHERE
                `id` = 31;
        ");

        $this->execute("
            UPDATE `tt_folders`
            SET
                `name` = 'Отложенные'
            WHERE
                `pk` = (SELECT `pk` FROM `tt_states` WHERE `id` = 31)
        ");
    }

    public function down()
    {
        echo "m150930_101610_onlime_devices_folder cannot be reverted.\n";

        return true;
    }
}