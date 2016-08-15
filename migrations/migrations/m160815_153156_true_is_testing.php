<?php

use app\models\TariffVoip;

class m160815_153156_true_is_testing extends \app\classes\Migration
{
    public function up()
    {
        $this->renameColumn(TariffVoip::tableName(), 'is_testing', 'is_default');
    }

    public function down()
    {
        $this->renameColumn(TariffVoip::tableName(), 'is_default', 'is_testing');
    }
}