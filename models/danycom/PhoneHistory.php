<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;
use app\dao\ActualNumberDao;

class PhoneHistory extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_phones_history';
    }
}
