<?php

namespace app\commands;

use app\classes\api\ApiPhone;
use app\helpers\DateTimeZoneHelper;
use app\models\Currency;
use app\models\CurrencyRate;
use DateTime;
use Exception;
use Yii;
use yii\console\Controller;
use yii\db\Query;

class SmsController extends Controller
{

    public function actionGet($days = 1)
    {
        $todayDate = new DateTime;
        $fromDate = clone $todayDate;
        $todayDate = $todayDate->format('Y-m-d');
        $fromDate = $fromDate->modify("-".$days.' day')->format('Y-m-d');
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $data = (new Query())->from('sms_send_byday')
                ->where('date between :dateStart and :dateFinish', [
                    ':dateStart' => $fromDate,
                    ':dateFinish' => $todayDate,
                    ])
                ->all(\Yii::$app->dbSms);

            foreach ($data as $index => $info) {
                Yii::$app->db->createCommand()->upsert('sms_stat', [
                    'sender' => $info['client_id'],
                    'count' => $info['smses'],
                    'date_hour' => $info['date'],
                ])->execute();
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            throw $e;
        }
    }
}
