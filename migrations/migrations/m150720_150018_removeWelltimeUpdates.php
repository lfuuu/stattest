<?php

class m150720_150018_removeWelltimeUpdates extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
          DROP TABLE IF EXISTS `welltime_updates`;
          DROP VIEW `welltime_updates`;
          DROP TABLE IF EXISTS `clients_test`;

          ALTER VIEW `usage_sms_gate` AS select sql_no_cache `u`.`id` AS `usage_id`,`c`.`client` AS `client`,`c`.`id` AS `client_id`,`u`.`actual_from` AS `actual_from`,`u`.`actual_to` AS `actual_to`,if((isnull(`u`.`param_value`) or (`u`.`param_value` = '')),ltrim(substr(`t`.`param_name`,(locate('=',`t`.`param_name`) + 1))),`u`.`param_value`) AS `sms_max`,`u`.`status` AS `status` from ((`nispd`.`clients` `c` join `nispd`.`usage_extra` `u` on((`u`.`client` = `c`.`client`))) join `nispd`.`tarifs_extra` `t` on(((`t`.`id` = `u`.`tarif_id`) and (`t`.`code` = 'sms_gate')))) WITH LOCAL CHECK OPTION;


        ");
    }

    public function down()
    {
        echo "m150720_150018_removeWelltimeUpdates cannot be reverted.\n";

        return false;
    }
}