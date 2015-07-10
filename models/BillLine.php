<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $pk
 * @property string $bill_no        номер счета, ссылка на счет
 * @property string $bill_date      дата счета
 * @property int    $sort           порядковый номер строки в счете
 * @property string $item           наименование позиции счета
 * @property string $item_id        идентификатор товара в 1с. ссылка на g_good
 * @property int    $code_1c        код строки счета в 1с
 * @property string $descr_id       идентификатор характеристики товара в 1с. ссылка на g_good_description
 * @property float  $amount         количество
 * @property int    $dispatch       количество отгружено
 * @property float  $price          цена за единицу. без ндс для счетов стата. с ндс для счетов 1с.
 * @property float  $sum            сумма с налогами в валюте счета
 * @property float  $sum_without_tax сумма без налогов в валюте счета
 * @property float  $sum_tax        сумма налогов в валюте счета
 * @property float  $discount       ??
 * @property float  $discount_set   сумма ручной скидки. актуально для 1с. сохраняется в 1с, синхронизируется в стат
 * @property float  $discount_auto  автоматическая скидка. актуально для 1с. рассчитывается в 1с, синхронизируется в стат
 * @property string $service        индентификатор типа услуги. Актуально для автогенерируемых счетов за периодические услуги.
 * @property float  $id_service     идентификатор услуги. Актуально для автогенерируемых счетов за периодические услуги.
 * @property string $date_from      период за который взымается плата. Актуально для абонентки
 * @property string $date_to        период за который взымается плата. Актуально для абонентки
 * @property int    $type           тип строки. значения: service, zalog, zadatok, good, all4net
 * @property string $gtd            ??  значения: beznal,nal,prov
 * @property string $contry_maker   Признак проведенности счета. 1 - проведен, влияет на балланс. 0 - не проведен, не влияет на баланс.
 * @property int    $country_id     Сумма не проведенного счета. Для проведенных счетов 0.
 * @property int    $tax_rate       Значение ставки налога
 * @property
 */
class BillLine extends ActiveRecord
{
    public static function tableName()
    {
        return 'newbill_lines';
    }

    public function calculateSum($priceIncludeVat)
    {
        $sum = $this->price * $this->amount - $this->discount_auto - $this->discount_set;

        if ($priceIncludeVat) {
            $this->sum = round($sum, 2);
            $this->sum_tax = round($this->tax_rate / (100.0 + $this->tax_rate) * $this->sum, 2);
            $this->sum_without_tax = $this->sum - $this->sum_tax;
        } else {
            $this->sum_without_tax = round($sum, 2);
            $this->sum_tax = round($this->sum_without_tax * $this->tax_rate / 100, 2);
            $this->sum = $this->sum_without_tax + $this->sum_tax;
        }
    }
}