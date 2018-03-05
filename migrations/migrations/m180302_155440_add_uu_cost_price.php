<?php

use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogResource;

/**
 * Class m180302_155440_add_uu_self_cost
 */
class m180302_155440_add_uu_cost_price extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountLogResource::tableName(), 'cost_price', $this->decimal(13, 4)->notNull()->defaultValue(0));
        $this->addColumn(AccountEntry::tableName(), 'cost_price', $this->decimal(13, 4)->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountLogResource::tableName(), 'cost_price');
        $this->dropColumn(AccountEntry::tableName(), 'cost_price');
    }
}
