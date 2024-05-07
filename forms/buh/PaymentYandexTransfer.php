<?php
namespace app\forms\buh;

use app\classes\Assert;
use app\classes\Form;
use app\classes\validators\AccountIdValidator;
use app\exceptions\ModelValidationException;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Payment;
use yii\db\Expression;

class PaymentYandexTransfer extends Form
{
    public $from_client_id;
    public $to_client_id;
    public $payment_id;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['from_client_id', 'to_client_id', 'payment_id'], 'integer'],
            [['from_client_id', 'to_client_id'], AccountIdValidator::class],
            ['payment_id', 'in', 'range' => array_keys($this->getPayments())]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'from_client_id' => 'c ЛС',
            'to_client_id' => 'на ЛС',
            'payment_id' => 'Платеж',
        ];
    }

    /**
     * Перенос платежа
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function transfer()
    {
        /** @var Payment $payment */
        $payment = Payment::findOne(['id' => $this->payment_id]);

        Assert::isObject($payment);

        $billQuery = Bill::find()
            ->where(['client_id' => $this->to_client_id])
            ->orderBy([
                'is_payed' => SORT_DESC,
                'bill_date' => SORT_DESC
            ]);

        $billQueryNotPayed = clone $billQuery;

        /** @var Bill $bill */
        $bill = $billQueryNotPayed
            ->andWhere(['is_payed' => [Bill::PAY_NOT_PAYED, Bill::PAY_PART_PAYED]])
            ->one();

        if (!$bill) {
            $billQueryPayed = clone $billQuery;
            $bill = $billQueryPayed
                ->andWhere(['is_payed' => Bill::PAY_FULL_PAYED])
                ->one();
        }

        if (!$bill) {
            $this->addError('to_client_id', 'У клиента нет счетов для переноса на них платежа');

            return false;
        }

        $payment->client_id = $this->to_client_id;
        $payment->bill_no = $payment->bill_vis_no = $bill->bill_no;

        if (!$payment->save()) {
            throw new ModelValidationException($payment);
        }

        ClientAccount::dao()->updateBalance($this->from_client_id);
        ClientAccount::dao()->updateBalance($this->to_client_id);

        return true;
    }

    /**
     * Получение списка Яндекс-платежей клиента
     *
     * @return array
     */
    public function getPayments()
    {
        return Payment::find()
            ->where([
                'client_id' => $this->from_client_id,
                'type' => [Payment::TYPE_ECASH, Payment::TYPE_API],
            ])
            ->select(['name' => new Expression("CONCAT(sum, ' ', currency, ' - (', payment_date, ') - ', comment)"), 'id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id')
            ->column();
    }
}