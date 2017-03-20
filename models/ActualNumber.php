<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\ActualNumberDao;

/**
 * Class ActualNumber
 * @property int $id
 * @property int $client_id
 * @property int $number
 * @property int $region
 * @property int $call_count
 * @property string $number_type
 * @property int $is_blocked
 * @property int $is_disabled
 * @property string $number7800
 * @property int $biller_version
 * @package app\models
 */
class ActualNumber extends ActiveRecord
{

    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\ActualNumber::className(),
        ];
    }

    public static function tableName()
    {
        return 'actual_number';
    }

    public static function dao()
    {
        return ActualNumberDao::me();
    }

}
