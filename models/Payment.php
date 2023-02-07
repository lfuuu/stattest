<?php

namespace app\models;

use app\modules\atol\behaviors\SendToOnlineCashRegister;
use app\classes\model\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\modules\uu\behaviors\RecalcRealtimeBalance;
use Yii;
use yii\db\ActiveQuery;

/**
 * Платёж
 *
 * @property int $id             идентификатор платежа
 * @property int $client_id      идентификатор лицевого счета
 * @property string $payment_no     номер платежа по данным внешней системы или банка
 * @property string $bill_no        счет, к которому привязан платеж
 * @property string $bill_vis_no    счет к которому платеж прикреплен
 * @property string $payment_date   дата отправки платежа
 * @property string $oper_date      дата получения платежа
 * @property float $payment_rate   курс конвертации валюты
 * @property int $type           тип платежа: bank - загружен из банк клиента, prov - введен вручную, ecash - оплата электронными деньгами, neprov - ??
 * @property string $ecash_operator значения: cyberplat, yandex. актуально если type = ecash
 * @property float $sum            сумма платежа. конвертируется из оригинальной суммы платежа в валюту лицевого счета
 * @property string $currency       валюта платежа. выставляется по валюте лицевого счета
 * @property float $original_sum       оригинальная сумма платежа
 * @property string $original_currency  оригинальная валюта платежа
 * @property float $comment        комментарий к платежу
 * @property string $add_date       дата и время внесения записи о платеже
 * @property float $add_user       пользователь, добавивший запись о платеже
 * @property float $bank           банк
 * @property float $payment_type   тип платежа: доход/расход
 *
 * @property-read Bill $bill счёт
 * @property-read ClientAccount $client
 * @property-read User $addUser
 * @property-read PaymentAtol $paymentAtol
 * @property-read PaymentStripe $paymentStripe
 * @property-read PaymentApiInfo $apiInfo
 */
class Payment extends ActiveRecord
{
    const TYPE_BANK = 'bank';
    const TYPE_PROV = 'prov';
    const TYPE_NEPROV = 'neprov';
    const TYPE_ECASH = 'ecash';
    const TYPE_CREDITNOTE = 'creditnote';
    const TYPE_TERMINAL = 'terminal';
    const TYPE_API = 'api';

    const BANK_CITI = 'citi';
    const BANK_MOS = 'mos';
    const BANK_URAL = 'ural';
    const BANK_SBER = 'sber';
    const BANK_RAIFFEISEN = 'raiffeisen';
    const BANK_PROMSVIAZBANK = 'promsviazbank';
    const BANK_TATRA = 'tatra';
    const BANK_RAIFFEISEN_AUSTRIA = 'raiffeisen_austria';
    const BANK_OTP = 'otp';

    const ECASH_CYBERPLAT = 'cyberplat';
    const ECASH_YANDEX = 'yandex';
    const ECASH_PAYPAL = 'paypal';
    const ECASH_SBERBANK = 'sberbank';
    const ECASH_QIWI = 'qiwi';
    const ECASH_STRIPE = 'stripe';
    const ECASH_SBERBANK_ONLINE_MOBILE = 'sberOnlineMob';

    const PAYMENT_TYPE_INCOME = 1;
    const PAYMENT_TYPE_OUTCOME = 2;

    public static $paymentTypes = [
        self::PAYMENT_TYPE_INCOME => 'Доход',
        self::PAYMENT_TYPE_OUTCOME => 'Расход',
    ];

    public static $types = [
        self::TYPE_PROV => 'Check',
        self::TYPE_NEPROV => 'Cash',
        self::TYPE_TERMINAL => 'Terminal',
        self::TYPE_BANK => 'Bank transfer',
        self::TYPE_ECASH => 'Electronic money',
        self::TYPE_CREDITNOTE => 'Credit Note',
        self::TYPE_API => 'API',
    ];


    public static $banks = [
        self::BANK_CITI => 'Сити Банк',
        self::BANK_MOS => 'Банк Москвы',
        self::BANK_URAL => 'УралСиб',
        self::BANK_SBER => 'Сбербанк',
        self::BANK_PROMSVIAZBANK => 'Промсвязьбанк',
        self::BANK_RAIFFEISEN => 'Raiffeisen Bank Zrt.',
        self::BANK_TATRA => 'TATRA BANKA A.S.',
        self::BANK_RAIFFEISEN_AUSTRIA => 'Raiffeisenlandesbank Niederösterreich-Wien AG (Austria)',
        self::BANK_OTP => 'ОТП Банк',
    ];

    public static $ecash = [
        self::ECASH_CYBERPLAT => 'Cyberplat',
        self::ECASH_YANDEX => 'YandexMoney',
        self::ECASH_PAYPAL => 'PayPal',
        self::ECASH_SBERBANK => 'Sberbank',
        self::ECASH_QIWI => 'Qiwi',
        self::ECASH_STRIPE => 'Stripe',
        self::ECASH_SBERBANK_ONLINE_MOBILE => 'sberOnlineMob',
    ];

    public $isNeedToSendAtol = false;
    public $isIdentificationPayment = false;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newpayments';
    }

    /**
     * Флаги транзации
     *
     * @return array
     */
    public function transactions()
    {
        return [
            'default' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
	];
    }

    /**
     * Поведение
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'RecalcRealtimeBalance' => RecalcRealtimeBalance::class, // Пересчитать realtime баланс при поступлении платежа
            'SendToOnlineCashRegister' => SendToOnlineCashRegister::class, // В соответствии с ФЗ−54 отправить данные в онлайн-кассу. А она сама отправит чек покупателю и в налоговую
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'ЛС',
            'payment_no' => 'Номер платежа',
            'bill_no' => 'Счет',
            'bill_vis_no' => 'Прикрепленый счет',
            'payment_date' => 'Дата совершения  платежа',
            'oper_date' => 'Дата получения платежа',
            'payment_rate' => 'Курс конвертации валюты',
            'type' => 'Тип платежа',
            'ecash_operator' => 'оператор электронных денег',
            'sum' => 'Сумма',
            'currency' => 'Валюта',
            'original_sum' => 'Оригинальная сумма',
            'original_currency' => 'Оригинальная валюта',
            'comment' => 'Комментарий',
            'add_date' => 'Дата занесения платежа',
            'add_user' => 'Добавивший пользователь',
            'bank' => 'Банк',
        ];
    }

    /**
     * Получение счета по платежу
     *
     * @return ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }

    /**
     * Получение ЛС
     *
     * @return ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'client_id']);
    }

    /**
     * Добавивший платеж пользователь
     *
     * @return ActiveQuery
     */
    public function getAddUser()
    {
        return $this->hasOne(User::class, ['id' => 'add_user']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPaymentAtol()
    {
        return $this->hasOne(PaymentAtol::class, ['id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getApiInfo()
    {
        return $this->hasOne(PaymentApiInfo::class, ['payment_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPaymentStripe()
    {
        return $this->hasOne(PaymentStripe::class, ['payment_id' => 'id']);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            Transaction::dao()->insertByPayment($this);

            ImportantEvents::create(ImportantEventsNames::PAYMENT_ADD,
                ImportantEventsSources::SOURCE_STAT, [
                    'client_id' => $this->client_id,
                    'sum' => round($this->sum, 2),
                    'currency' => $this->currency,
                    'user_id' => Yii::$app->user->id,
                    'is_identification_payment' => $this->isIdentificationPayment,
                ]);
        } else {
            Transaction::dao()->updateByPayment($this);
        }
    }


    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeDelete()
    {
        Transaction::dao()->deleteByPaymentId($this->id);
        ImportantEvents::create(ImportantEventsNames::PAYMENT_DELETE,
            ImportantEventsSources::SOURCE_STAT, [
                'client_id' => $this->client_id,
                'sum' => round($this->sum, 2),
                'currency' => $this->currency,
                'user_id' => Yii::$app->user->id
            ]);
        LogBill::dao()->log($this->bill_no, "Удаление платежа ({$this->id}), на сумму: {$this->sum}");

        return parent::beforeDelete();
    }
}
