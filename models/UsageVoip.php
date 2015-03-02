<?php
namespace app\models;

use app\classes\bill\VoipBiller;
use yii\db\ActiveRecord;
use app\queries\UsageVoipQuery;
use DateTime;

/**
 * @property int $id
 * @property
 */
class UsageVoip extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'usage_voip';
    }

    public static function find()
    {
        return new UsageVoipQuery(get_called_class());
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VoipBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return null;
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_VOIP;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }
}

