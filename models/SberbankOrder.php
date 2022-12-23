<?php

namespace app\models;

use app\classes\behaviors\CreatedAt;
use app\classes\helpers\DependecyHelper;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;

/**
 * Class SberbankOrder
 *
 * @property integer id
 * @property string created_at
 * @property string order_id
 * @property string bill_no
 * @property integer payment_id
 * @property integer status
 * @property string order_url
 * @property string info_json
 * @property-read Bill bill
 */
class SberbankOrder extends ActiveRecord
{
    const STATUS_NOT_REGISTERED = 0;
    const STATUS_REGISTERED = 1;
    const STATUS_PAYED = 2;

    const ID_SEMAFOR = 1020;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'order_sberbank';
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::class,
        ];
    }

    /**
     * Связка со счетом
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }

    /**
     * Создание платежа на основе сбербанковского заказа
     *
     * @param array $info
     * @throws \Exception
     */
    public function makePayment($info)
    {
        $mutexLockKey = 'sber-payment-lock-key';
        if (!\Yii::$app->mutex->acquire($mutexLockKey, DependecyHelper::TIMELIFE_MINUTE)) {
            throw new \RuntimeException("Can't get lock", 500);
        }

        if (isset($info['orderNumber'])) {
            if (
                Payment::find()->where(['bill_no' => $info['orderNumber']])->exists()
                || (
                    isset($info['authRefNum'])
                    && $info['authRefNum']
                    && Payment::find()->where([
                        'payment_no' => $info['authRefNum'],
                        'client_id' => $this->bill->client_id
                    ])->exists()
                )
            ) {
                \Yii::$app->mutex->release($mutexLockKey);
                return;
            }
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $now = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)));

            $payment = new Payment();

            $bill = $this->bill;


            $payment->payment_no = $info['authRefNum'];
            $payment->client_id = $bill->client_id;
            $payment->bill_no = $payment->bill_vis_no = $bill->bill_no;
            $payment->add_date = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $payment->payment_date = $payment->oper_date = $now->format(DateTimeZoneHelper::DATE_FORMAT);
            $payment->type = Payment::TYPE_ECASH;
            $payment->ecash_operator = Payment::ECASH_SBERBANK;
            $payment->comment = "Sberbank payment #" . $bill->client_id . '-' . $bill->bill_no . ' (' . $info['cardAuthInfo']['cardholderName'] . ')';
            $payment->sum = $payment->original_sum = $info['amount'] / 100;
            $payment->currency = Currency::getIdByCode($info['currency']);

            if (!$payment->save()) {
                throw new ModelValidationException($payment);
            }

            $payment->refresh();

            $this->payment_id = $payment->id;
            $this->info_json = json_encode($info, JSON_UNESCAPED_UNICODE);
            $this->status = SberbankOrder::STATUS_PAYED;
            if (!$this->save()) {
                throw new ModelValidationException($this);
            }

            $transaction->commit();
            \Yii::$app->mutex->release($mutexLockKey);
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->mutex->release($mutexLockKey);
            \Yii::error($e);
            throw $e;
        }
    }
}
