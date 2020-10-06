<?php

use app\classes\Migration;
use app\models\danycom\Info;

/**
 * Class m201006_165045_danycom_dc_info_file_link
 */
class m201006_165045_danycom_dc_info_file_link extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Info::tableName(),'file_link', $this->string(1024)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Info::tableName(),'file_link');
    }
}
