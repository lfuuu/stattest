<?php

class m161017_141702_tech_port_4g extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn(\app\models\TechPort::tableName(), "port_type", "enum('backbone','dedicated','pppoe','pptp','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON', 'megafon_4G') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'dedicated'");
    }

    public function down()
    {
        $this->alterColumn(\app\models\TechPort::tableName(), "port_type", "enum('backbone','dedicated','pppoe','pptp','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'dedicated'");
    }
}