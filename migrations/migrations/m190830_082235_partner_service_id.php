<?php

use app\models\PartnerRewards;

/**
 * Class m190830_082235_partner_service_id
 */
class m190830_082235_partner_service_id extends \app\classes\Migration
{
    protected $idxKeyName = null;
    /**
     * Up
     */
    public function init()
    {
        $this->idxKeyName = 'idx-'.PartnerRewards::tableName().'-reward_service_type_id-reward_service_id';
    }

    public function safeUp()
    {
        $this->addColumn(PartnerRewards::tableName(), 'reward_service_type_id', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn(PartnerRewards::tableName(), 'reward_service_id', $this->integer()->notNull()->defaultValue(0));
        $this->createIndex(
            $this->idxKeyName,
            PartnerRewards::tableName(),
            ['reward_service_type_id', 'reward_service_id']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex($this->idxKeyName, PartnerRewards::tableName());
        $this->dropColumn(PartnerRewards::tableName(), 'reward_service_type_id');
        $this->dropColumn(PartnerRewards::tableName(), 'reward_service_id');
    }
}
