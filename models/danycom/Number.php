<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;
use app\dao\ActualNumberDao;

/**
 * Class Number
 * @property int $account_id
 * @property string $number
 * @property string $region
 * @property string $operator
 */
class Number extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_number';
    }
}
