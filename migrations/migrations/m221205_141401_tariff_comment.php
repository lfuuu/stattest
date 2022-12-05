<?php

/**
 * Class m221205_141401_tariff_comment
 */
class m221205_141401_tariff_comment extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\modules\uu\models\Tariff::tableName(),'comment', $this->text()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\modules\uu\models\Tariff::tableName(),'comment');
    }
}
