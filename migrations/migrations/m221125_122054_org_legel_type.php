<?php

/**
 * Class m221125_122054_org_legel_type
 */
class m221125_122054_org_legel_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->renameColumn(\app\models\EntryPoint::tableName(), 'org_type', 'legal_type');
        $this->update(\app\models\EntryPoint::tableName(), ['legal_type' => '']);
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'org_type');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->renameColumn(\app\models\EntryPoint::tableName(), 'legal_type', 'org_type');
        $this->addColumn(\app\models\ClientContragent::tableName(), 'org_type', $this->string()->notNull()->defaultValue(''));
    }
}
