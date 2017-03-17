<?php

namespace app\classes\behaviors;

use app\exceptions\ModelValidationException;
use app\models\Bill;
use app\models\billing\Locks;
use app\models\ClientAccount;
use app\models\PaymentOrder;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


class CheckBillPaymentOverdue extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => "checkOverdue",
            ActiveRecord::EVENT_BEFORE_UPDATE => "checkOverdue",
            Bill::TRIGGER_CHECK_OVERDUE => "checkOverdue" // необходимо сохранить модель, если тригер её изменил
        ];
    }

    /**
     * Проверяет необходимость установки/снятия флага просрочки платежа и блокировки ЛС
     *
     * @param Event $event
     */
    public function checkOverdue(Event $event)
    {
        /** @var Bill $bill */
        $bill = $this->owner;

        $tz = new \DateTimeZone($bill->clientAccount->timezone_name);

        $now = new \DateTime('now', $tz);

        $payUntilDate = new \DateTime($bill->pay_bill_until, $tz);

        $billType = Bill::dao()->getDocumentType($bill->bill_no);

        $isStatBill = ($billType['type'] == Bill::DOC_TYPE_BILL && $billType['bill_type'] == Bill::TYPE_STAT);

        if ($isStatBill && $bill->is_payed == Bill::STATUS_PAID_IN_PART) {
            $payment = PaymentOrder::find()
                ->select(['sum'])
                ->where(['bill_no' => $bill->bill_no])
                ->scalar() ?: 0;
            $paymentPercent = $bill->sum ? ($payment * 100 / $bill->sum) : 100;
        }

        $isOverdue = (int)(
            $isStatBill &&
            $payUntilDate < $now &&
            (
                $bill->is_payed == Bill::STATUS_NOT_PAID || // полностью не оплачен счет (красный)
                ($bill->is_payed == Bill::STATUS_PAID_IN_PART && $paymentPercent < Bill::PERCENT_PAYMENT_PAY) // меньше 95%
            )
        );

        if ($isOverdue != $bill->is_pay_overdue) {

            $bill->is_pay_overdue = $isOverdue;
            $bill->isSetPayOverdue = $isOverdue;

            $this->_checkClientAccount($bill->clientAccount, $isOverdue);
        }
    }

    /**
     * Установка в ЛС флага о блокировке по причине просрочки оплаты счета
     *
     * @param ClientAccount $account
     * @param bool $isOverdue
     * @throws ModelValidationException
     */
    private function _checkClientAccount(ClientAccount $account, $isOverdue)
    {
        $isSetPayOverdue = (int)($isOverdue ?: Bill::find()
            ->where([
                'client_id' => $account->id
            ])
            ->andWhere([
                '!=', 'id', $this->owner->id
            ])
            ->max('is_pay_overdue'));

        $account = ClientAccount::findOne(['id' => $account->id]); // нужна модель ЛС без истории

        if ($account->is_bill_pay_overdue == $isSetPayOverdue) {
            return;
        }

        $account->is_bill_pay_overdue = $isSetPayOverdue;

        if ($isSetPayOverdue) {
            // $account->voip_disabled = 1; // временно отключим саму блокировку
        } else {
            $lock = Locks::findOne(['client_id' => $account->id]);

            if (!$lock || (!$lock->is_overran && !$lock->is_mn_overran)) { // снимаем блокировку, если нет других
                // $account->voip_disabled = 0;
            }
        }

        if (!$account->save()) {
            throw new ModelValidationException($account);
        }
    }
}
