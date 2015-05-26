<?php
namespace app\models;

use app\dao\TariffVoipDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TariffVoip extends ActiveRecord
{
    public static function tableName()
    {
        return 'tarifs_voip';
    }


    public static function dao()
    {
        return TariffVoipDao::me();
    }
}