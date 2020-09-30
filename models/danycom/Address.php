<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;
use app\dao\ActualNumberDao;

/**
 * Class Address
 * @property int $account_id
 * @property string $address
 * @property string $post_code
 */
class Address extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_address';
    }
}
