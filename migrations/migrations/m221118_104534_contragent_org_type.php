<?php

/**
 * Class m221118_104534_contragent_org_type
 */
class m221118_104534_contragent_org_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\ClientContragent::tableName(), 'org_type', $this->string()->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'org_type');
    }
}
