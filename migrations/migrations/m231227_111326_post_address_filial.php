<?php

/**
 * Class m231227_111326_post_address_filial
 */
class m231227_111326_post_address_filial extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\ClientContragent::tableName(), 'post_address_filial', $this->string(1024)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'post_address_filial');
    }
}
