<?php

use app\models\UsageVoip;

/**
 * Class m190123_091640_usage_comment
 */
class m190123_091640_usage_comment extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(UsageVoip::tableName(), 'usage_comment', $this->string()->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(UsageVoip::tableName(), 'usage_comment');
    }
}
