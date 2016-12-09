<?php

use app\models\ClientCounter;
use app\models\LkClientSettings;

class m161123_141529_day_limit_mn extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(LkClientSettings::tableName(), 'day_limit_mn_sent', $this->timestamp()->defaultValue(null));
        $this->addColumn(LkClientSettings::tableName(), 'is_day_limit_mn_sent', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn(ClientCounter::tableName(), 'amount_mn_day_sum', $this->integer()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn(LkClientSettings::tableName(), 'day_limit_mn_sent');
        $this->dropColumn(LkClientSettings::tableName(), 'is_day_limit_mn_sent');
        $this->dropColumn(ClientCounter::tableName(), 'amount_mn_day_sum');
    }
}