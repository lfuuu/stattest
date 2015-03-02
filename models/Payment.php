<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id             идентификатор платежа
 * @property int    $client_id      идентификатор лицевого счета
 * @property string $payment_no     номер платежа по данным внешней системы или банка
 * @property string $bill_no        счет, к которому привязан платеж
 * @property string $bill_vis_no    счет к которому платеж прикреплен
 * @property string $payment_date   счета без НДС для счетов стата и с НДС для счетов 1С. Только для проведенных счетов. Для не проведенных - 0
 * @property string $oper_date      признак оплаченности счета 0 - не оплачен, 1 - ??, 2 - ??, 3 - ??
 * @property float  $payment_rate   курс конвертации валюты
 * @property int    $type           тип платежа: bank - загружен из банк клиента, prov - введен вручную, ecash - оплата электронными деньгами, neprov - ??
 * @property float  $ecash_operator значения: cyberplat, yandex. актуально если type = ecash
 * @property float  $sum            сумма платежа. конвертируется из оригинальной суммы платежа в валюту лицевого счета
 * @property string $currency       валюта платежа. выставляется по валюте лицевого счета
 * @property float  $original_sum       оригинальная сумма платежа
 * @property string $original_currency  оригинальная валюта платежа
 * @property float  $comment        комментарий к платежу
 * @property string $add_date       дата и время внесения записи о платеже.
 * @property float  $add_user       пользователь добавивший запись о платеже.
 * @property float  $bank           ??
 * @property
 */
class Payment extends ActiveRecord
{
    const TYPE_BANK   = 'bank';
    const TYPE_PROV   = 'prov';
    const TYPE_NEPROV = 'neprov';
    const TYPE_ECASH  = 'ecash';

    const BANK_CITI = 'citi';
    const BANK_MOS  = 'mos';
    const BANK_URAL = 'ural';
    const BANK_SBER = 'sber';

    const ECASH_CYBERPLAT = 'Cyberplat';
    const ECASH_YANDEX    = 'Yandex';

    public static $types = [
        self::TYPE_PROV => 'Проведенный нал.',
        self::TYPE_NEPROV => 'Не проведенный нал.',
        self::TYPE_BANK => 'Банк',
        self::TYPE_ECASH => 'Электронные деньги',
    ];

    public static $banks = [
        self::BANK_CITI => 'Сити Банк',
        self::BANK_MOS  => 'Банк Москвы',
        self::BANK_URAL => 'УралСиб',
        self::BANK_SBER => 'Сбербанк',
    ];

    public static $ecash = [
        self::ECASH_CYBERPLAT => 'Cyberplat',
        self::ECASH_YANDEX    => 'Яндекс.Деньги',
    ];

    public static function tableName()
    {
        return 'newpayments';
    }

    public function transactions()
    {
        return [
            'default' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            Transaction::dao()->insertByPayment($this);
        } else {
            Transaction::dao()->updateByPayment($this);
        }
    }


    public function beforeDelete()
    {
        Transaction::dao()->deleteByPaymentId($this->id);

        LogBill::dao()->log($this->bill_no, "Удаление платежа ({$this->id}), на сумму: {$this->sum}");

        return parent::beforeDelete();
    }

}