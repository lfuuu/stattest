<?php

class m150720_172049_extend_incomegoods_mask extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE `tt_types` SET `states` = 135291469824 WHERE `code` = 'incomegoods';
        ");

        $this->execute("
            UPDATE `tt_states` SET `deny` = 100931731456 WHERE `id` = 39;
        ");
    }

    public function down()
    {
        $this->execute("
            UPDATE `tt_states` SET `deny` = 0 WHERE `id` = 39;
        ");
        $this->execute("
            UPDATE `tt_types` SET `states` = 100931731456 WHERE `code` = 'incomegoods';
        ");

        return false;
    }
}