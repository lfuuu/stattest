<?php

/**
 * Class m240318_120016_payment_info_upd
 */
class m240318_120016_payment_info_upd extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        foreach (['getter_inn', 'getter_bik', 'getter_bank', 'getter_account'] as $f) {
            $this->alterColumn(\app\models\PaymentInfo::tableName(), $f, $this->string(128)->defaultValue(''));
        }

        $this->addColumn(\app\models\PaymentInfo::tableName(), 'created_at', $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        foreach (['getter_inn', 'getter_bik', 'getter_bank', 'getter_account'] as $f) {
            $this->alterColumn(\app\models\PaymentInfo::tableName(), $f, $this->string(128)->defaultValue('')->notNull());
        }

        $this->dropColumn(\app\models\PaymentInfo::tableName(), 'created_at');
    }
}
