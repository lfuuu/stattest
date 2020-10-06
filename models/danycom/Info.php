<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;
use app\dao\ActualNumberDao;

/**
 * Class Info
 * @property int $account_id
 * @property string $tariff
 * @property string $temp
 * @property string $delivery_type
 * @property string $file_link
 */
class Info extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_info';
    }
}
