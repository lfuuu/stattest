<?php

use app\models\ClientAccount;

class m160919_111518_clients_voip_limit_mn_day extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ClientAccount::tableName();

        $this->addColumn($tableName, 'voip_limit_mn_day', $this->integer(11)->defaultValue(0)->notNull());
        $this->addColumn($tableName, 'voip_is_mn_day_calc', $this->integer(1)->defaultValue(1)->notNull());
    }

    public function down()
    {
        $tableName = ClientAccount::tableName();

        $this->dropColumn($tableName, 'voip_limit_mn_day');
        $this->dropColumn($tableName, 'voip_is_mn_day_calc');
    }
}