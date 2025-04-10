<?php

/**
 * Class m250410_080658_invoice_org_odx
 */
class m250410_080658_invoice_org_odx extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createIndex('idx-' . \app\models\Invoice::tableName() . '-org-date', \app\models\Invoice::tableName(), ['organization_id', 'date']);
        $this->createIndex('idx-' . \app\models\Invoice::tableName() . 'org-inv_date', \app\models\Invoice::tableName(), ['organization_id', 'invoice_date']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropIndex('idx-' . \app\models\Invoice::tableName() . '-org-date', \app\models\Invoice::tableName());
        $this->dropIndex('idx-' . \app\models\Invoice::tableName() . 'org-inv_date', \app\models\Invoice::tableName());
    }
}
