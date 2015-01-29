<?php
namespace app\models;

use app\classes\bill\EmailBiller;
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

    public function getBiller(DateTime $date)
    {
        return new EmailBiller($this, $date);
    }

    public function getTariff()
    {
        return null;
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_EMAIL;
    }
}