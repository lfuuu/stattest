<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;

/**
 * Class PhoneHistory
 * @property string $process_id
 * @property string $phone_ported
 * @property string $state
 * @property string $date_ported
 */
class PhoneHistory extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_phones_history';
    }
}
