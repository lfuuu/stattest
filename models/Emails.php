<?php
namespace app\models;

use app\classes\bill\EmailBiller;
use app\classes\transfer\EmailServiceTransfer;
use app\dao\services\EmailsServiceDao;
use yii\db\ActiveRecord;
use app\queries\UsageQuery;
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

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public static function dao()
    {
        return EmailsServiceDao::me();
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

    public static function getTypeTitle()
    {
        return 'E-mail';
    }

    public function getTypeDescription()
    {
        return $this->local_part . '@' . $this->domain;
    }

}
