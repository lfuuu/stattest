<?php

/**
 * Class m181122_153615_roistat_channel
 */
class m181122_153615_roistat_channel extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\TroubleRoistat::tableName(), 'roistat_visit', $this->string());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\TroubleRoistat::tableName(), 'roistat_channel_id', $this->integer());
    }
}
