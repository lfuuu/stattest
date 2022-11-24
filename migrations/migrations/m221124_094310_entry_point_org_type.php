<?php

/**
 * Class m221124_094310_entry_point_org_type
 */
class m221124_094310_entry_point_org_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\EntryPoint::tableName(), 'org_type', $this->string(128)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\EntryPoint::tableName(), 'org_type');
    }
}