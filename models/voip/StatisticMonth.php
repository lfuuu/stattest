<?php
namespace app\models\voip;

use app\classes\behaviors\CreatedAt;
use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\model\HistoryActiveRecord;
use app\dao\VoipRegistryDao;
use app\models\City;
use app\models\Country;
use yii\db\ActiveRecord;

/**
 * Class StatisticMonth
 *
 * @property integer $account_id
 * @property string $date
 * @property integer $count
 * @property integer $cost
 */
class StatisticMonth extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'stat_voip_month';
    }
}
