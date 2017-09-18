<?php

namespace app\models;

use app\classes\bill\EmailBiller;
use app\classes\model\ActiveRecord;
use app\dao\services\EmailsServiceDao;
use app\helpers\usages\UsageEmailHelper;
use app\models\usages\UsageInterface;
use app\queries\UsageQuery;
use DateTime;

/**
 * @property int $id
 * @property-read UsageEmailHelper $helper
 */
class UsageEmails extends ActiveRecord implements UsageInterface
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'emails';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return EmailsServiceDao
     */
    public static function dao()
    {
        return EmailsServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return EmailBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new EmailBiller($this, $date, $clientAccount);
    }

    /**
     * @return null
     */
    public function getTariff()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_EMAIL;
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    /**
     * @return UsageEmailHelper
     */
    public function getHelper()
    {
        return new UsageEmailHelper($this);
    }

}
