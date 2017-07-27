<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\dao\LogBillDao;

/**
 * Class LogBill
 *
 * @property int $id
 * @property string $bill_no
 * @property string $ts
 * @property int $user_id
 * @property string $comment
 */
class LogBill extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'log_newbills';
    }

    /**
     * DAO
     *
     * @return LogBillDao
     */
    public static function dao()
    {
        return LogBillDao::me();
    }
}