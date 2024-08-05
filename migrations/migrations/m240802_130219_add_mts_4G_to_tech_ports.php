<?php

/**
 * Class m240802_130219_add_mts_4G_to_tech_ports
 */
class m240802_130219_add_mts_4G_to_tech_ports extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE `tech_ports` CHANGE `port_type` `port_type` ENUM('backbone','dedicated','pppoe','pptp','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON','megafon_4G','mts_4G') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'dedicated';");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE `tech_ports` CHANGE `port_type` `port_type` ENUM('backbone','dedicated','pppoe','pptp','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON','megafon_4G') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'dedicated';");
    }
}
