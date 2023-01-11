<?php

/**
 * Class m230111_133536_paiment_api_log
 */
class m230111_133536_paiment_api_log extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\PaymentApiInfo::tableName(), 'log', $this->text()->notNull()->defaultValue(''));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\PaymentApiInfo::tableName(), 'log');
    }
}
