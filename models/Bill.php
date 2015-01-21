<?php
namespace app\models;

use app\dao\BillDao;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $bill_no        номер счета для отображения
 * @property string $bill_date      дата счета
 * @property int    $client_id      id лицевого счета
 * @property string $currency       валюта. значения: USD, RUB
 * @property int    $is_approved    Признак проведенности счета. 1 - проведен, влияет на балланс. 0 - не проведен, не влияет на баланс.
 * @property string $sum            итоговая сумма, влияющая на баланс. не включает задаток. не включает не проведенные строки
 * @property string $sum_with_unapproved  итоговая сумма. не включает задаток. включает не проведенный строки
 * @property int    $is_payed       признак оплаченности счета 0 - не оплачен, 1 - ??, 2 - ??, 3 - ??
 * @property string $comment
 * @property int    $inv2to1        ??
 * @property float  $inv_rub        ??
 * @property string $postreg        ?? date
 * @property int    $courier_id     ??
 * @property string $nal            ??  значения: beznal,nal,prov
 * @property string $sync_1c        ??  значения: yes, no
 * @property string $push_1c        ??  значения: yes, no
 * @property string $state_1c       ??
 * @property string $is_rollback    1 - счет на возврат. 0 - обычный
 * @property string $payed_ya       ??
 * @property string $editor         ??  значения: stat, admin
 * @property int    $is_lk_show     ??
 * @property string $doc_date       ??
 * @property int    $is_user_prepay ??
 * @property string $bill_no_ext        ??
 * @property string $bill_no_ext_date   ??

 * @property ClientAccount $clientAccount   ??
 * @property
 */
class Bill extends ActiveRecord
{
    public static function tableName()
    {
        return 'newbills';
    }

    public static function dao()
    {
        return BillDao::me();
    }

    public function getLines()
    {
        return $this->hasMany(BillLine::className(), ['bill_no' => 'bill_no']);
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_id']);
    }
}