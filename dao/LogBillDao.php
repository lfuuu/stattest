<?php
namespace app\dao;

use app\helpers\DateTimeZoneHelper;
use Yii;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\Bill;
use app\models\LogBill;
use app\models\User;

/**
 * Class LogBillDao
 *
 * @method static LogBillDao me($args = null)
 */
class LogBillDao extends Singleton
{
    /**
     * Логирование счета
     *
     * @param Bill|string $billOrBillNo
     * @param string $message
     * @param integer|null $userId
     */
    public function log($billOrBillNo, $message, $userId = null)
    {
        $log = new LogBill();

        if ($billOrBillNo instanceof Bill) {
            $log->bill_no = $billOrBillNo->bill_no;
        } elseif (is_string($billOrBillNo)) {
            $log->bill_no = $billOrBillNo;
        } else {
            Assert::isUnreachable();
        }

        if (!$userId) {
            $userId = Yii::$app->has('user') && Yii::$app->user->getId() ?
                Yii::$app->user->getId() :
                User::SYSTEM_USER_ID;
        }

        $log->ts = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $log->user_id = $userId;
        $log->comment = $message;
        $log->save();
    }

    public static function getLog($billNo)
    {
        return LogBill::find()
            ->select(['log_newbills.*', 'user_users.user'])
            ->leftJoin(['user_users' => User::tableName()], '`user_users`.`id` = `log_newbills`.`user_id`')
            ->where(['bill_no' => $billNo])
            ->orderBy(['ts' => SORT_DESC]);
    }
}
