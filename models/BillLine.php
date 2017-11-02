<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountEntry;

/**
 * Class BillLine
 *
 * @property int $pk
 * @property string $bill_no        номер счета, ссылка на счет
 * @property string $bill_date      дата счета
 * @property int $sort           порядковый номер строки в счете
 * @property string $item           наименование позиции счета
 * @property string $item_id        идентификатор товара в 1с. ссылка на g_good
 * @property int $code_1c        код строки счета в 1с
 * @property string $descr_id       идентификатор характеристики товара в 1с. ссылка на g_good_description
 * @property float $amount         количество
 * @property int $dispatch       количество отгружено
 * @property float $price          цена за единицу. без ндс для счетов стата. с ндс для счетов 1с.
 * @property float $sum            сумма с налогами в валюте счета
 * @property float $sum_without_tax сумма без налогов в валюте счета
 * @property float $sum_tax        сумма налогов в валюте счета
 * @property float $discount       ??
 * @property float $discount_set   сумма ручной скидки. актуально для 1с. сохраняется в 1с, синхронизируется в стат
 * @property float $discount_auto  автоматическая скидка. актуально для 1с. рассчитывается в 1с, синхронизируется в стат
 * @property string $service        индентификатор типа услуги. Актуально для автогенерируемых счетов за периодические услуги.
 * @property float $id_service     идентификатор услуги. Актуально для автогенерируемых счетов за периодические услуги.
 * @property string $date_from      начало периода, за который взимается плата. Актуально для абонентки
 * @property string $date_to        конец периода, за который взимается плата. Актуально для абонентки
 * @property string $gtd            ??  значения: beznal,nal,prov
 * @property string $contry_maker   Признак проведенности счета. 1 - проведен, влияет на балланс. 0 - не проведен, не влияет на баланс.
 * @property int $country_id     Сумма не проведенного счета. Для проведенных счетов 0.
 * @property int $tax_rate       Значение ставки налога
 * @property int $uu_account_entry_id
 *
 * @property int $type           тип строки. значения: service, zalog, zadatok, good, all4net
 * @property-read Bill $bill
 * @property-read AccountEntry $accountEntry
 */
class BillLine extends ActiveRecord
{

    const LINE_TYPE_SERVICE = 'service';
    const LINE_TYPE_ZALOG = 'zalog';
    const LINE_TYPE_ZADATOK = 'zadatok';
    const LINE_TYPE_GOOD = 'good';
    const LINE_TYPE_ALL4NET = 'all4net';

    public $isHistoryVersioning = false;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'newbill_lines';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::className(), ['bill_no' => 'bill_no']);
    }

    /**
     * @return bool|string
     */
    public function getType()
    {
        return
            $this->hasOne(Transaction::className(), ['bill_line_id' => 'pk'])
                ->select('transaction_type')
                ->scalar();
    }

    /**
     * @param boolean $priceIncludeVat
     */
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

    /**
     * @param array $lines
     * @param string $lang
     * @return array
     */
    public static function compactLines($lines, $lang)
    {
        $data = [];
        $idx = [];

        $accountTariffIds = [];

        array_walk($lines, function ($line) use (&$accountTariffIds) {
            if ($line['uu_account_entry_id']) {
                $accountTariffIds[] = $line['uu_account_entry_id'];
            }
        });

        $accountTariffs = AccountEntry::find()
            ->where(['id' => $accountTariffIds])
            ->indexBy('id')
            ->all();

        /** @var BillLine $line */
        foreach ($lines as $line) {
            /** @var AccountEntry $accountEntry */
            $accountEntry = $line['uu_account_entry_id'] && isset($accountTariffs[$line['uu_account_entry_id']]) ?
                $accountTariffs[$line['uu_account_entry_id']] :
                null;

            // Группируем строки счета по ставке НДС, дате начала и конца, по типу записи.
            // Ресурсы складываем. В подключении, абонентке и минималке - складываем ещё и по цене.
            $key = $accountEntry ?
                $accountEntry->vat_rate . '/' .
                ($accountEntry->type_id > 0 ? 1 : $line['price']) . '/' .
                $accountEntry->type_id . '/' .
                $accountEntry->date_from . '/' .
                $accountEntry->date_to :
                $line['pk']; // если нет привязки к accountEntry - выдаем как есть

            if (!isset($idx[$key])) {
                $idx[$key] = [
                    'first_line' => $line,
                    'first_account_entry' => $accountEntry,
                    'sums' => [
                        'price_without_vat' => 0,
                        'price_with_vat' => 0,
                        'vat' => 0
                    ]
                ];
            }

            $idx[$key]['sums']['amount'] += $line['amount'];
            $idx[$key]['sums']['sum'] += $line['sum'];
            $idx[$key]['sums']['sum_without_tax'] += $line['sum_without_tax'];
            $idx[$key]['sums']['sum_tax'] += $line['sum_tax'];
        }

        // Сортировка. Сначало подключение, абонентка и минималка. Потом ресурсы. Потом по названию.
        usort($idx, function ($a, $b) {
            $aTypeId = abs($a['first_account_entry']['type_id']);
            $bTypeId = abs($b['first_account_entry']['type_id']);

            if ($aTypeId != $bTypeId) {
                return $aTypeId > $bTypeId ? 1 : -1;
            }

            $aLine = $a['first_line'];
            $bLine = $b['first_line'];

            if ($aLine['item'] != $bLine['item']) {
                return strcmp($aLine['item'], $bLine['item']);
            }

            return 0;
        });

        foreach ($idx as $row) {
            $line = $row['first_line'];
            $line['amount'] = $row['sums']['amount'];
            $line['sum'] = $row['sums']['sum'];
            $line['sum_without_tax'] = $row['sums']['sum_without_tax'];
            $line['sum_tax'] = $row['sums']['sum_tax'];

            /** @var AccountEntry $accountEntry */
            $accountEntry = $row['first_account_entry'];
            if ($accountEntry) {
                $line['item'] = $accountEntry->getFullName($lang, false);
            }

            $data[] = $line;
        }

        return $data;
    }

}