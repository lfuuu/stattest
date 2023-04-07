<?php

namespace app\classes\api;


use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Organization;
use app\models\Payment;
use app\modules\uu\models\AccountTariff;
use yii\web\Response;

class SberbankOnline
{
    const ACTION_CHECK = 'check';
    const ACTION_PAY = 'pay';

    const SUM_MIN = 10;
    const SUM_MAX = 15000;

    const SBER_ORGANIZATION_MCN_TELECOM = 1;
    const SBER_ORGANIZATION_AB_SERVICE = 2;

    const SBER_ORGANIZATION_TO_ORGANIZATION = [
        self::SBER_ORGANIZATION_MCN_TELECOM => Organization::MCN_TELECOM,
        self::SBER_ORGANIZATION_AB_SERVICE => Organization::AB_SERVICE_MARCOMNET,
    ];

    const DATE_FORMAT = 'YmdHis';

    private $resultCode = 0; // Успешное завершение операции

    private $organizationId = null;

    /**
     * SberbankOnline constructor.
     */
    public function __construct()
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        $result = $this->do(\Yii::$app->request->isPost ? \Yii::$app->request->post() : \Yii::$app->request->get());
        \Yii::$app->response->data = ['result' => $this->resultCode] + $result;
        \Yii::$app->end();
    }

    private function do($data)
    {
        try {
            if (!isset($data['command']) || !in_array($data['command'], [self::ACTION_CHECK, self::ACTION_PAY])) {
                $this->resultCode = 2; // Неизвестный тип запроса
                throw new \InvalidArgumentException('command error');
            }

            if (!isset($data['account']) || !preg_match('/^\d+$/', $data['account'])) {
                $this->resultCode = 4; // Неверный формат идентификатора Плательщика
                throw new \InvalidArgumentException('Invalid format of the Payer\'s identifier');
            }

            $this->checkSum($data);
            $this->checkOrganization($data);

            switch ($data['command']) {
                case self::ACTION_CHECK:
                    $this->checkTransaction($data, true);
                    $result = $this->checkAccount($data);
                    $this->checkTransaction($data); // для полной проверки необходима установка account

                    return $result;
                    break;

                case self::ACTION_PAY:
                    $this->checkDate($data);
                    $this->checkAccount($data);
                    $this->checkTransaction($data);
                    return $this->pay($data);
                    break;

                default:
                    new \Exception('wtf?');
            }


        } catch (\Exception $e) {
            return ['comment' => $e->getMessage()];
        }
    }

    private function checkSum($data)
    {
        if (!isset($data['sum'])) {
            $this->resultCode = 9; // Неверная сумма платежа
            throw new \InvalidArgumentException('Invalid payment amount');
        }

        if ($data['sum'] < self::SUM_MIN) {
            $this->resultCode = 10; // Сумма слишком мала
            throw new \InvalidArgumentException('The amount is too small');
        }

        if ($data['sum'] > self::SUM_MAX) {
            $this->resultCode = 11; // Сумма слишком велика
            throw new \InvalidArgumentException('The amount is too big');
        }
    }

    private function checkOrganization($data)
    {
        if (!isset($data['serv_id'])) {
            return;
        }

        if (!isset(self::SBER_ORGANIZATION_TO_ORGANIZATION[$data['serv_id']])) {
            return;
        }

        $this->organizationId = self::SBER_ORGANIZATION_TO_ORGANIZATION[$data['serv_id']];
    }

    private function checkAccount(&$data)
    {
        /**
         * command=check    – запрос на проверку состояния Плательщика
         * txn_id=1        – внутренний номер платежа в системе Банка, используется для сверки платежей и решения спорных вопросов
         * account=49578    – идентификатор Плательщика в информационной системе Клиента
         * sum=1.00        – сумма к зачислению на лицевой счет Плательщика
         */

        $voipAccountId = AccountTariff::find()
            ->where(['voip_number' => ($data['account'] ?? 0)])
            ->andWhere(['NOT', ['tariff_period_id' => null]])
            ->select('client_account_id')
            ->scalar();

        $account = ClientAccount::find()
            ->alias('c')
            ->joinWith('clientContractModel co')
            ->where([
                'c.id' => ($voipAccountId ? $voipAccountId : $data['account'] ?? 0),
//                'co.organization_id' => Organization::MCN_TELECOM,
            ])
            ->one();

        /** @var ClientAccount $account */
        if ($account) {
            $data['account_obj'] = $account;
            return [
                'txn_id' => $data['txn_id'],
                'comment' => 'Payer found',
                'fio' => $account->contragent->name,
                'balance' => $account->billingCounters->realtimeBalance,
                'info' => 'Аваносвый платеж',
            ];
        }

        $this->resultCode = 3; // Плательщик не найден
        throw new \InvalidArgumentException('Payer not found');
    }

    private function pay($data)
    {
        /**
         * command=pay    – запрос на пополнение баланса Плательщика
         * txn_id=123456789012– внутренний номер платежа в системе Банка
         * txn_date=20180619120133 – дата учета платежа в системе Банка
         * account=49578 – идентификатор Плательщика в информационной системе Клиента
         * sum=10.45    – сумма к зачислению на лицевой счет Плательщика (дробное число с точностью до сотых, в качестве разделителя используется «.» точка)
         */

        /** @var ClientAccount $account */
        $account = $data['account_obj'];
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $bill = Bill::dao()->createBillOnSum($account->id, $data['sum'], $account->currency);

            /** @var \DateTimeImmutable $paymentDate */
            $paymentDate = $data['txn_date_obj'];
            $addDate = new \DateTimeImmutable('now');
            $payment = new Payment;
            $payment->setAttributes([
                'client_id' => $account->id,
                'payment_no' => $data['txn_id'],
                'bill_no' => $bill->bill_no,
                'bill_vis_no' => $bill->bill_no,
                'payment_date' => $paymentDate->format(DateTimeZoneHelper::DATE_FORMAT),
                'oper_date' => $paymentDate->format(DateTimeZoneHelper::DATE_FORMAT),
                'payment_rate' => 1,
                'type' => Payment::TYPE_ECASH,
                'ecash_operator' => Payment::ECASH_SBERBANK_ONLINE_MOBILE,
                'sum' => $data['sum'],
                'currency' => $bill->currency,
                'original_sum' => $data['sum'],
                'original_currency' => $bill->currency,
                'comment' => 'Sberbank.Online payment #' . $data['txn_id'] . ' at ' . $paymentDate->format(DateTimeZoneHelper::DATETIME_FORMAT),
                'add_date' => $addDate->format(DateTimeZoneHelper::DATETIME_FORMAT),
                'organization_id' => $this->organizationId,
            ], false);

            if (!$payment->save()) {
                throw new ModelValidationException($payment);
            }

            ClientAccount::dao()->updateBalance($payment->client_id);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e);
            $this->resultCode = 1; //Временная ошибка. Повторите запрос позже
            throw new \InvalidArgumentException('Temporary error. Please try again later');
        }

        return [
            'ext_id' => $payment->id,
            'pay_date' => $addDate->format(self::DATE_FORMAT),
            'sum' => $payment->sum,
            'comment' => 'Payment successful',
        ];
    }

    public function checkDate(&$data)
    {
        if (
            !isset($data['txn_date'])
            || strlen($data['txn_date']) != 14
            || substr($data['txn_date'], 0, 2) != 20
            || !($date = \DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $data['txn_date']))
        ) {
            $this->resultCode = 12;
            throw new \InvalidArgumentException('Invalid date value');
        }

        $data['txn_date_obj'] = $date;
    }

    public function checkTransaction($data, $isOnlyFormat = false)
    {
        if (
            !isset($data['txn_id'])
            || !$data['txn_id']
            || strlen($data['txn_id']) > 20
            || preg_replace('/\d+/', '', $data['txn_id']) != ''
        ) {
            $this->resultCode = 6; // Неверное значение идентификатора транзакции
            throw new \InvalidArgumentException('Invalid transaction ID value');
        }

        if ($isOnlyFormat) {
            return;
        }

        if (Payment::find()
            ->where([
                    'client_id' => $data['account'],
                    'payment_no' => $data['txn_id']]
            )->exists()
        ) {
            $this->resultCode = 8;
            throw new \InvalidArgumentException('Duplicate transaction');
        }
    }
}