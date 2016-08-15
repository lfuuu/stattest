<?php
namespace app\models;

use app\models\tariffs\TariffInterface;
use yii\db\ActiveRecord;

/**
 * @property int $id
 */
class TariffInternet extends ActiveRecord implements TariffInterface
{
    const STATUS_ADSL_SU = 'adsl_su';

    public static function tableName()
    {
        return 'tarifs_internet';
    }

    public function isTest()
    {
        return $this->status == self::STATUS_TEST;
    }

    public function getHelper()
    {
        return null;
    }
}