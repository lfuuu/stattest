<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id             идентификатор платежа
 * @property int    $client_id      идентификатор лицевого счета
 * @property string $payment_no     номер платежа по данным внешней системы или банка
 * @property string $bill_no        ??
 * @property string $bill_vis_no    ??
 * @property string $payment_date   счета без НДС для счетов стата и с НДС для счетов 1С. Только для проведенных счетов. Для не проведенных - 0
 * @property string $oper_date      признак оплаченности счета 0 - не оплачен, 1 - ??, 2 - ??, 3 - ??
 * @property float  $payment_rate   ??
 * @property int    $type           тип платежа: bank - загружен из банк клиента, prov - введен вручную, ecash - оплата электронными деньгами, neprov - ??
 * @property float  $ecash_operator значения: uniteller, cyberplat, yandex. актуально если type = ecash
 * @property float  $sum_rub        сумма платежа конвертированная в рубли
 * @property string $currency       не используется. всегда RUR.
 * @property float  $comment        комментарий к платежу
 * @property string $add_date       дата и время внесения записи о платеже.
 * @property float  $add_user       пользователь добавивший запись о платеже.
 * @property string $push_1c        ??
 * @property float  $sync_1c        ??
 * @property float  $bank           ??
 * @property
 */
class Payment extends ActiveRecord
{
    public static function tableName()
    {
        return 'newpayments';
    }
}