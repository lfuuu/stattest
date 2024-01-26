<?php

/**
 * Class m240126_105251_contragent_signatory
 */
class m240126_105251_contragent_signatory extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\ClientContragent::tableName(), 'is_take_signatory', $this->tinyInteger()->notNull()->defaultValue(0));
        $this->addColumn(\app\models\ClientContragent::tableName(), 'signatory_position', $this->string(128)->notNull()->defaultValue(''));
        $this->addColumn(\app\models\ClientContragent::tableName(), 'signatory_fio', $this->string(128)->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'is_take_signatory');
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'signatory_position');
        $this->dropColumn(\app\models\ClientContragent::tableName(), 'signatory_fio');
    }
}
