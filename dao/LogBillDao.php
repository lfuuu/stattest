<?php
namespace app\dao;

use Yii;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\Bill;
use app\models\LogBill;
use app\models\User;

/**
 * @method static LogBillDao me($args = null)
 * @property
 */
class LogBillDao extends Singleton
{
    public function log($billOrBillNo, $message)
    {
        $log = new LogBill();

        if ($billOrBillNo instanceof Bill) {
            $log->bill_no = $billOrBillNo->bill_no;
        } elseif (is_string($billOrBillNo)) {
            $log->bill_no = $billOrBillNo;
        } else {
            Assert::isUnreachable();
        }

        $log->ts = (new \DateTime())->format('Y-m-d H:i:s');
        $log->user_id = Yii::$app->has('user') && Yii::$app->user->getId() ? Yii::$app->user->getId() : User::SYSTEM_USER_ID;
        $log->comment = $message;
        $log->save();
    }
}