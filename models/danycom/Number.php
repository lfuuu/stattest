<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;

/**
 * Class Number
 * @property int $account_id
 * @property string $number
 * @property string $region
 * @property string $operator
 * @property string $date_ported
 */
class Number extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_number';
    }

}
