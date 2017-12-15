<?php

namespace app\modules\payments\forms;


use app\classes\Form;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\Currency;
use app\models\Payment;
use app\models\UsageVoip;
use app\modules\payments\classes\QiwiExitObject;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

class QiwiForm extends Form
{
    const SUM_MIN = 10; // минимальная сумма платежа
    const SUM_MAX = 15000; // максимальная сумма платежа

    public $command = null;
    public $sum = null;
    public $txn_id = null;
    public $txn_date = null;
    public $account = null; // account_id

    /** @var \DateTime */
    private $_txnDate = null;

    const COMMAND_CHECK = 'check';
    const COMMAND_PAY = 'pay';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['command', 'sum', 'txn_id', 'account'], 'required', 'message' => QiwiExitObject::ERROR_UNKNOWN],
            ['command', 'in', 'range' => ['check', 'pay'], 'message' => QiwiExitObject::ERROR_UNKNOWN, 'skipOnError' => true],
            ['sum', 'number', 'min' => self::SUM_MIN, 'max' => self::SUM_MAX,
                'message' => QiwiExitObject::ERROR_UNKNOWN,
                'tooSmall' => QiwiExitObject::ERROR_SUM_SMALL,
                'tooBig' => QiwiExitObject::ERROR_SUM_BIG,
                'skipOnError' => true
            ],
            ['account', 'accountValidator', 'skipOnError' => true],
            ['txn_date', 'required', 'skipOnError' => true, 'when' => function () {
                return $this->command == self::COMMAND_PAY;
            }],
            ['txn_date', 'tnxDateValidator', 'skipOnError' => true, 'when' => function () {
                return $this->command == self::COMMAND_PAY;
            }],
        ];
    }

    /**
     * Валидатор ЛС
     *
     * @throws QiwiExitObject
     */
    public function accountValidator($attr)
    {
        if (!preg_match('/^(\d{3,6}|(\+?7|8)?\d{10})$/', $this->account, $matches)) {
            throw new QiwiExitObject(QiwiExitObject::ERROR_ACCOUNT_FORMAT);
        }

        if (strlen($this->account) <= 6) {
            if ($this->_checkAccount()) {
                return;
            }
        } else {
            if ($this->_checkNumber()) {
                return;
            }
        }

        $this->addError($attr, QiwiExitObject::ERROR_ACCOUNT_NOT_FOUND);
    }

    /**
     * Проверка по номеру ЛС
     *
     * @return bool
     */
    private function _checkAccount()
    {
        $account = ClientAccount::findOne(['id' => $this->account]);

        if (!$account) {
            $this->addError('account', QiwiExitObject::ERROR_ACCOUNT_NOT_FOUND);
            return false;
        }

        if (!in_array($account->contract->business_process_status_id, BusinessProcessStatus::PAY_AVAILABLE_STATUSES_TELEKOM_MAINTENANCE)) {
            $this->addError('account', QiwiExitObject::ERROR_ACCOUNT_BLOCKED);
            return false;
        }

        return true;
    }

    /**
     * Проверка по номеру теелфона в услуге
     *
     * @return bool
     */
    private function _checkNumber()
    {
        /** @var UsageVoip $usage */
        $usage = UsageVoip::find()
            ->actual()
            ->phone($this->account)
            ->one();

        if ($usage) {
            $this->account = $usage->clientAccount->id;
            return true;
        }

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()
            ->where([
                'service_type_id' => ServiceType::ID_VOIP,
                'voip_number' => $this->account
            ])
            ->andWhere(['IS NOT', 'tariff_period_id', null])
            ->one();

        if ($accountTariff) {
            $this->account = $accountTariff->client_account_id;
            return true;
        }

        return false;
    }

    /**
     * Валидатор даты платежа
     *
     * @throws QiwiExitObject
     */
    public function tnxDateValidator($attr)
    {
        if (!($this->_txnDate = \DateTime::createFromFormat('YmdGis', $this->txn_date))) {
            $this->addError($attr, QiwiExitObject::ERROR_UNKNOWN, 'date format error');
        }
    }

    /**
     * Проверить возможность приема платежа
     *
     * @return QiwiExitObject
     */
    public function doCheck()
    {
        // если мы здесь, значит, все валидации пройдены
        return new QiwiExitObject(QiwiExitObject::OK, 'Account exists');
    }

    /**
     * Принять платеж
     *
     * @return QiwiExitObject
     * @throws ModelValidationException
     */
    public function doPay()
    {
        if ($payment = Payment::findOne(['payment_no' => $this->txn_id])) {
            return new QiwiExitObject(QiwiExitObject::OK, '', ['prv_txn' => $payment->id]);
        }

        $bill = Bill::dao()->createBillOnSum($this->account, $this->sum, Currency::RUB);

        $payment = new Payment;
        $payment->setAttributes([
            'client_id' => $this->account,
            'payment_no' => $this->txn_id,
            'bill_no' => $bill->bill_no,
            'bill_vis_no' => $bill->bill_no,
            'payment_date' => $this->_txnDate->format(DateTimeZoneHelper::DATE_FORMAT),
            'oper_date' => $this->_txnDate->format(DateTimeZoneHelper::DATE_FORMAT),
            'payment_rate' => 1,
            'type' => Payment::TYPE_ECASH,
            'ecash_operator' => Payment::ECASH_QIWI,
            'sum' => $this->sum,
            'currency' => $bill->currency,
            'original_sum' => $this->sum,
            'original_currency' => $bill->currency,
            'comment' => 'Qiwi payment #' . $this->txn_id . ' at ' . $this->_txnDate->format(DateTimeZoneHelper::DATETIME_FORMAT),
            'add_date' => (new \DateTime('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT),
        ], false);

        if (!$payment->save()) {
            throw new ModelValidationException($payment);
        }

        ClientAccount::dao()->updateBalance($payment->client_id);

        return new QiwiExitObject(QiwiExitObject::OK, '', ['prv_txn' => $payment->id]);
    }

}