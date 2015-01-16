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
 * @property float  $sum            сумма строки счета. цена * количество
 * @property float  $discount       ??
 * @property float  $discount_set   сумма ручной скидки. актуально для 1с. сохраняется в 1с, синхронизируется в стат
 * @property float  $discount_auto  автоматическая скидка. актуально для 1с. рассчитывается в 1с, синхронизируется в стат
 * @property float  $all4net_price  ??
 * @property string $service        индентификатор типа услуги. Актуально для автогенерируемых счетов за периодические услуги.
 * @property float  $id_service     идентификатор услуги. Актуально для автогенерируемых счетов за периодические услуги.
 * @property string $date_from      период за который взымается плата. Актуально для абонентки
 * @property string $date_to        период за который взымается плата. Актуально для абонентки
 * @property int    $type           тип строки. значения: service, zalog, zadatok, good, all4net
 * @property string $gtd            ??  значения: beznal,nal,prov
 * @property string $contry_maker   Признак проведенности счета. 1 - проведен, влияет на балланс. 0 - не проведен, не влияет на баланс.
 * @property int    $country_id     Сумма не проведенного счета. Для проведенных счетов 0.
 * @property string $is_price_includes_tax  1 - цена включает налоги, 0 - цена указана без налогов
 * @property string $tax_type_id            идентификатор ставки налога. Ссылка на tax_type
 * @property float  $sum_without_tax        сумма без налогов в валюте счета
 * @property float  $sum_tax                сумма налогов в валюте счета
 * @property float  $sum_with_tax           сумма с налогами в валюте счета
 * @property float  $doc_sum_without_tax        сумма без налогов для документов (в рублях)
 * @property float  $doc_sum_tax                сумма налогов для документов (в рублях)
 * @property float  $doc_sum_with_tax           сумма с налогами для документов (в рублях)
 * @property
 */
class BillLine extends ActiveRecord
{
    public static function tableName()
    {
        return 'newbill_lines';
    }

    public function calcSum(Bill $bill)
    {
//        if ($bill->bill_date < '2014-11-01') {
//            $this->calcSumCompatibility($bill);
//            return;
//        }

        $this->sum_without_tax = round($this->price * $this->amount, 2);
        $this->sum_tax = round($this->sum_without_tax * TaxType::rate($this->tax_type_id), 2);
        $this->sum_with_tax = $this->sum_without_tax + $this->sum_tax;

        $rate = $this->getRate($bill);

        $this->doc_sum_without_tax = round($this->sum_without_tax * $rate, 2);
        $this->doc_sum_tax = round($this->doc_sum_without_tax * TaxType::rate($this->tax_type_id), 2);;
        $this->doc_sum_with_tax = $this->doc_sum_without_tax + $this->doc_sum_tax;
    }

    public function calcSumCompatibility(Bill $bill)
    {
        $this->sum_without_tax = round($this->price * $this->amount, 2);
        $this->sum_tax = round($this->sum_without_tax * TaxType::rate($this->tax_type_id), 2);
        $this->sum_with_tax = round($this->sum_without_tax + $this->sum_tax, 2);

        $rate = $this->getRate($bill);

        $this->doc_sum_without_tax = $this->price * $this->amount * $rate;
        $this->doc_sum_tax = $this->doc_sum_without_tax * TaxType::rate($this->tax_type_id);
        $this->doc_sum_with_tax = $this->doc_sum_without_tax + $this->doc_sum_tax;

        $this->doc_sum_without_tax = round($this->doc_sum_without_tax, 2);
        $this->doc_sum_tax = round($this->doc_sum_tax, 2);
        $this->doc_sum_with_tax = round($this->doc_sum_with_tax, 2);
    }

    private function getRate(Bill $bill)
    {
        return 1;
    }

}