<?php
namespace app\models;

use app\classes\bill\EmailBiller;
use app\classes\transfer\EmailServiceTransfer;
use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id
 * @property
 */
class Emails extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'emails';
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new EmailBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return null;
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_EMAIL;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getTransferHelper()
    {
        return new EmailServiceTransfer($this);
    }
}