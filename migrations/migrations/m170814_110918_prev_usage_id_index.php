<?php
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;

/**
 * Class m170814_110918_prev_usage_id_index
 */
class m170814_110918_prev_usage_id_index extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createIndex('idx-prev_usage_id', UsageVirtpbx::tableName(), 'prev_usage_id');
        $this->createIndex('idx-prev_usage_id', UsageVoip::tableName(), 'prev_usage_id');
        $this->createIndex('idx-prev_usage_id', AccountTariff::tableName(), 'prev_usage_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('idx-prev_usage_id', UsageVirtpbx::tableName());
        $this->dropIndex('idx-prev_usage_id', UsageVoip::tableName());
        $this->dropIndex('idx-prev_usage_id', AccountTariff::tableName());
    }
}
