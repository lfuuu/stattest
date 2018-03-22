<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property integer id
 * @property string client
 * @property string state
 * @property string bill_no
 * @property string last_send
 * @property string message
 */
class BillSend extends ActiveRecord
{
    const STATE_VIEWED = 'viewed';

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newbill_send';
    }
}
