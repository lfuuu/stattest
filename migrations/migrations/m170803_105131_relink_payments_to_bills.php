<?php

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;

/**
 * Class m170803_105131_relink_payments_to_bills
 */
class m170803_105131_relink_payments_to_bills extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \yii\db\Exception
     * @throws ModelValidationException
     */
    public function safeUp()
    {
        ClientAccount::dao()->relinkPaymentToBill();
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
