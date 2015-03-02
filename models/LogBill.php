<?php
namespace app\models;

use app\dao\LogBillDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class LogBill extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_newbills';
    }

    public static function dao()
    {
        return LogBillDao::me();
    }
}