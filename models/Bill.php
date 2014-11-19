<?php
namespace app\models;

use app\dao\BillDao;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $bill_no        номер счета для отображения
 * @property string $bill_date      дата счета
 * @property int    $client_id      id лицевого счета
 * @property string $currency       валюта. значения: USD, RUR
 * @property float  $sum сумма      счета без НДС для счетов стата и с НДС для счетов 1С. Только для проведенных счетов. Для не проведенных - 0
 * @property int    $is_payed       признак оплаченности счета 0 - не оплачен, 1 - ??, 2 - ??, 3 - ??
 * @property string $comment
 * @property int    $inv2to1        ??
 * @property float  $inv_rur        ??
 * @property float  $inv1_rate      ??
 * @property string $inv1_date      ??
 * @property float  $inv2_rate      ??
 * @property string $inv2_date      ??
 * @property float  $inv3_rate      ??
 * @property string $inv3_date      ??
 * @property float  $gen_bill_rur   ??
 * @property float  $gen_bill_rate  ??
 * @property string $gen_bill_date  ??
 * @property string $postreg        ?? date
 * @property int    $courier_id     ??
 * @property string $nal            ??  значения: beznal,nal,prov
 * @property int    $cleared_flag   Признак проведенности счета. 1 - проведен, влияет на балланс. 0 - не проведен, не влияет на баланс.
 * @property string $cleared_sum    Сумма не проведенного счета. Для проведенных счетов 0.
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
 * @property string $sum_total      итоговая сумма, влияющая на баланс. не включает задаток. не включает не проведенные строки
 * @property string $sum_total_with_unapproved  итоговая сумма. не включает задаток. включает не проведенный строки
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

    public function setRateForInvoice1($rate)
    {
        $this->inv_rur = null;
        $this->inv1_rate = $rate;
        $this->inv1_date = date('Y-m-d');
        return $this;
    }

    public function setRateForInvoice2($rate)
    {
        $this->inv_rur = null;
        $this->inv2_rate = $rate;
        $this->inv2_date = date('Y-m-d');
        return $this;
    }

    public function setRateForInvoice3($rate)
    {
        $this->inv_rur = null;
        $this->inv3_rate = $rate;
        $this->inv3_date = date('Y-m-d');
        return $this;
    }

    public function setRateForBill($rate)
    {
        $this->gen_bill_rur = null;
        $this->gen_bill_rate = $rate;
        $this->gen_bill_date = date('Y-m-d');
        return $this;
    }

    public function setSumRubForInvoice($sumRub)
    {
        $this->inv_rur = round($sumRub, 2);
        $this->inv1_date = date('Y-m-d');
        $this->inv2_date = date('Y-m-d');
        $this->inv3_date = date('Y-m-d');
        $this->inv1_rate = null;
        $this->inv2_rate = null;
        $this->inv3_rate = null;
        return $this;
    }

    public function setSumRubForBill($sumRub)
    {
        $this->gen_bill_rur = $sumRub;
        $this->gen_bill_date = date('Y-m-d');
        $this->gen_bill_rate = null;
        return $this;
    }

}