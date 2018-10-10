<?php

use app\models\UsageTrunk;

/**
 * Class m181005_071913_add_comment_to_usage_trunk
 */
class m181005_071913_add_comment_to_usage_trunk extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(UsageTrunk::tableName(), 'comment', $this->text());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(UsageTrunk::tableName(), 'comment');
    }
}
