<?php

use app\models\Organization;

/**
 * Class m190722_142813_organization_invoice_counter
 */
class m190722_142813_organization_invoice_counter extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Organization::tableName(), 'invoice_counter_range_id', $this->smallInteger()->notNull()->defaultValue(Organization::INVOICE_COUNTER_RANGE_ID_MONTH));
        $this->update(Organization::tableName(), ['invoice_counter_range_id' => Organization::INVOICE_COUNTER_RANGE_ID_YEAR], ['organization_id' => [
            Organization::TEL2TEL_KFT, Organization::TEL2TEL_GMBH, Organization::WL_MCN_INNONET
        ]]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Organization::tableName(), 'invoice_counter_range_id');
    }
}
