<?php
namespace app\models;

use app\classes\bill\EmailBiller;
use app\classes\transfer\EmailServiceTransfer;
use app\dao\services\EmailsServiceDao;
use yii\db\ActiveRecord;
use app\queries\UsageQuery;
use DateTime;
use app\helpers\usages\UsageEmailHelper;

/**
 * @property int $id
 * @property
 */
class UsageEmails extends ActiveRecord implements Usage
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

    /**
     * @param $usage
     * @return EmailServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new EmailServiceTransfer($usage);
    }

    /**
     * @return UsageEmailHelper
     */
    public function getHelper()
    {
        return new UsageEmailHelper($this);
    }

}
