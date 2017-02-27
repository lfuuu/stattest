<?php
namespace app\models;

use app\dao\LogBillDao;
use yii\db\ActiveRecord;

/**
 * Class LogBill
 *
 * @property int id
 * @property string bill_no
 * @property string ts
 * @property int user_id
 * @property string comment
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