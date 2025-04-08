<?php

/**
 * Class m250408_114946_agent_nds
 */
class m250408_114946_agent_nds extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Organization::tableName(), 'is_agent_tax_rate', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn(\app\modules\uu\models\Tariff::tableName(), 'agent_tax_rate', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Organization::tableName(), 'is_agent_tax_rate');
        $this->dropColumn(\app\modules\uu\models\Tariff::tableName(), 'agent_tax_rate');
    }
}
