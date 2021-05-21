<?php

namespace app\models;

use app\classes\helpers\DependecyHelper;
use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ResourceModel;
use yii\caching\TagDependency;

/**
 * Расчётная проводка
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
 * @property int $cost_price Себестоимость
 *
 * @property int $type           тип строки. значения: service, zalog, zadatok, good, all4net
 * @property-read Bill $bill
 * @property-read AccountEntry $accountEntry
 * @property-read AccountTariff $accountTariff
 */
class BillLine extends ActiveRecord
{

    const LINE_TYPE_SERVICE = 'service';
    const LINE_TYPE_ZALOG = 'zalog';
    const LINE_TYPE_ZADATOK = 'zadatok';
    const LINE_TYPE_GOOD = 'good';
    const LINE_TYPE_ALL4NET = 'all4net';

    const DATE_DEFAULT = '0000-00-00';

    public $isHistoryVersioning = false;

    protected $billId = null;

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
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountEntry()
    {
        return $this->hasOne(AccountEntry::class, ['id' => 'uu_account_entry_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::class, ['id' => 'id_service']);
    }

    /**
     * @return bool|string
     */
    public function getType()
    {
        return
            $this->hasOne(Transaction::class, ['bill_line_id' => 'pk'])
                ->select('transaction_type')
                ->scalar();
    }

    public function getParentId()
    {
        return $this->billId ?: $this->getBill()->select('id')->scalar();
    }

    public function setParentId($billId)
    {
        $this->billId = $billId;
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
     * @return bool
     */
    public function isResource()
    {
        if ($this->uu_account_entry_id) {
            return $this->accountEntry->type_id > 0;
        }

        return $this->bill->bill_date > $this->date_to;
    }

    public function isResourceCalls()
    {
        if (!$this->isResource()) {
            return false;
        }

        return in_array($this->accountEntry->tariffResource->resource_id, array_keys(ResourceModel::$calls));
    }

    /**
     * @param array $lines
     * @param string $lang
     * @param boolean $isPriceIncludeVat
     * @return array
     * @internal param mixed $untypedLines
     * @internal param array $lines
     * @throws \yii\base\InvalidConfigException
     */
    public static function compactLines($lines, $lang, $isPriceIncludeVat)
    {
        $cacheKey = md5(serialize($lines));

        if (\Yii::$app->cache->exists($cacheKey)) {
            return \Yii::$app->cache->get($cacheKey);
        }

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
                        'sum' => 0,
                        'sum_without_tax' => 0,
                        'sum_tax' => 0,
                        'amount' => 0,
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
            $line = is_object($row['first_line']) ? (clone $row['first_line']) : $row['first_line'];
            $line['amount'] = $row['sums']['amount'];
            $line['sum'] = $row['sums']['sum'];
            $line['sum_without_tax'] = $row['sums']['sum_without_tax'];
            $line['sum_tax'] = $row['sums']['sum_tax'];

            /** @var AccountEntry $accountEntry */
            $accountEntry = $row['first_account_entry'];
            if ($accountEntry) {
                $line['item'] = $accountEntry->getFullName($lang, false);
            }

            $line['price'] = ($isPriceIncludeVat ? $line['sum'] : $line['sum_without_tax']) / $line['amount'];

            // установка правильных значений НДС и суммы
            $oLine = new self;
            $oLine->setAttributes(method_exists($line, 'getAttributes') ? $line->getAttributes() : $line, false);
            $oLine->calculateSum($isPriceIncludeVat);
            $line = $oLine->getAttributes();

            $line['outprice'] = $line['price'];

            $data[] = $line;
        }

        \Yii::$app->cache->set($cacheKey, $data, DependecyHelper::DEFAULT_TIMELIFE, (new TagDependency(['tags' => [DependecyHelper::TAG_BILL]])));

        return $data;
    }

    /**
     * Сумма по линиям
     *
     * @param array $lines
     * @return float
     */
    public static function getSumsLines($lines)
    {
        return array_reduce($lines,
            function ($data, $line) {
                /** @var BillLine $line */
                $data['sum'] += is_array($line) ? $line['sum'] : $line->sum;
                $data['sum_tax'] += is_array($line) ? $line['sum_tax'] : $line->sum_tax;
                $data['sum_without_tax'] += is_array($line) ? $line['sum_without_tax'] : $line->sum_without_tax;
                return $data;
            }, ['sum' => 0, 'sum_tax' => 0, 'sum_without_tax' => 0]);
    }

    /**
     * Унификация с uuBill. НДС
     *
     * @return float
     */
    public function getVat()
    {
        return $this->sum_tax;
    }

    /**
     * Унификация с uuBill. Ставка НДС
     * @return int
     */
    public function getVat_rate()
    {
        return $this->tax_rate;
    }

    /**
     * Унификация с uuBill. Сумма без НДС
     *
     * @return float
     */
    public function getPrice_without_vat()
    {
        return $this->sum_without_tax;
    }

    /**
     * Унификация с uuBill. Сумма НДС
     *
     * @return float
     */
    public function getPrice_with_vat()
    {
        return $this->sum;
    }

    /**
     * Унификация с uuBill. Кол-во
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Унификация с uuBill. Название проводки
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->item;
    }

    /**
     * Унификация с uuBill.
     *
     * @return string
     */
    public function getTypeUnitName()
    {
        return '';
    }

    public static function refactLinesWithFourOrderFacture(Bill $bill, $lines)
    {
        $ret_x = [
            'is_four_order' => true,

            'bill_no' => '',
            'sort' => 1,
            'item' => 'Предварительная оплата ',
            'service' => 'usage_ip_ports',
            'id_service' => 2945,
            'date_from' => '',
            'date_to' => '',
            'type' => 'service',
            'id' => '',
            'ts_from' => '',
            'ts_to' => '',
            'tax' => 0,
            'country_id' => 0,
            'okvd_code' => 0,
            'okvd' => "",
            'amount' => 1,
            'price' => '',
            'outprice' => '',
            'tax_type_id' => null,
            'sum' => '',
            'sum_without_tax' => 0,
            'sum_tax' => 0,
        ];

        $billTable = Bill::tableName();
        $paymentTable = Payment::tableName();

        $pay = \Yii::$app->db->createCommand("
			SELECT
				sum(`subq`.`sum`) `sum`,
				`subq`.`type`
			FROM
				(
					SELECT
						IFNULL(`np`.`sum`,`nb`.`sum`) `sum`,
						IF(IFNULL(`np`.`sum`,FALSE),'PAY','BILL') `type`
					FROM
						{$billTable} `nb`
					LEFT JOIN
						{$paymentTable} `np`
					ON
						`np`.`bill_no` = `nb`.`bill_no`
					WHERE
						`nb`.`bill_no` = '" . $bill->bill_no . "'
				) subq
			GROUP BY
				`subq`.`type`
		")->queryOne();

        if ($pay['type'] == 'PAY') {

            $tax_rate = $bill->clientAccount->getTaxRate();
            $ret_x['tax_rate'] = $tax_rate;

            $ret_x['sum'] = $pay['sum'];
            $ret_x['sum_tax'] = $pay['sum'] * $tax_rate / (100 + $tax_rate);
        }


        foreach ($lines as $key => &$item) {
            if (
                $item['sum'] > 0
                && preg_match('/^\s*Абонентская\s+плата|^\s*Поддержка\s+почтового\s+ящика|^\s*Виртуальная\s+АТС|^\s*Перенос|^\s*Выезд|^\s*Сервисное\s+обслуживание|^\s*Хостинг|^\s*Подключение|^\s*Внутренняя\s+линия|^\s*Абонентское\s+обслуживание|^\s*Услуга\s+доставки|^\s*Виртуальный\s+почтовый|^\s*Размещение\s+сервера|^\s*Настройка[0-9a-zA-Zа-яА-Я]+АТС|^Дополнительный\sIP[\s\-]адрес|^Поддержка\sпервичного\sDNS|^Поддержка\sвторичного\sDNS|^Аванс\sза\sподключение\sинтернет-канала|^Администрирование\sсервер|^Обслуживание\sрабочей\sстанции|^Оптимизация\sсайта|^Неснижаемый\sостаток/', $item['item'])
            ) {
                $item['item'] = str_replace('Абонентская', 'абонентскую', str_replace('плата', 'плату', $item['item']));
                $item['item'] = str_replace('Поддержка', 'поддержку', $item['item']);
                $item['item'] = str_replace('Виртуальная', 'виртуальную', $item['item']);
                $item['item'] = str_replace('Перенос', 'перенос', $item['item']);
                $item['item'] = str_replace('Выезд', 'выезд', $item['item']);
                $item['item'] = str_replace('Сервисное', 'сервисное', $item['item']);
                $item['item'] = str_replace('Хостинг', 'хостинг', $item['item']);
                $item['item'] = str_replace('Подключение', 'подключение', $item['item']);
                $item['item'] = str_replace('Внутренняя линия', 'внутреннюю линию', $item['item']);
                $item['item'] = str_replace('Услуга', 'услугу', $item['item']);
                $item['item'] = str_replace('Виртуальный', 'виртуальный', $item['item']);
                $item['item'] = str_replace('Размещение', 'размещение', $item['item']);
                $item['item'] = str_replace('Аванс за', '', $item['item']);
                $item['item'] = str_replace('Оптимизация', 'оптимизацию', $item['item']);
                $item['item'] = str_replace('Обслуживание', 'обслуживание', $item['item']);
                $item['item'] = str_replace('Администрирование', 'администрирование', $item['item']);

                $item['item'] = 'Авансовый платеж за ' . $item['item'];

                $ret_x['item'] .= $item['item'] . ";<br />";
                $ret_x['bill_no'] = $item['bill_no'];
                $ret_x['date_from'] = $item['date_from'];
                $ret_x['date_to'] = $item['date_to'];
                $ret_x['id'] = $item['pk'];
                $ret_x['date_from'] = $item['date_from'];
                $ret_x['date_to'] = $item['date_to'];

                if ($pay['type'] == 'BILL') {
                    $ret_x['sum'] += $item['sum'];
                    $ret_x['sum_tax'] += $item['sum_tax'];
                }
            } else {
                if ($pay['type'] == 'PAY' && $item['type'] <> 'zalog') {
                    $ret_x['sum'] -= $item['sum'];
                    $ret_x['sum_tax'] -= $item['sum_tax'];
                }
                if ($item['type'] == 'zadatok') {
                    $ret_x['sum'] += $item['sum'];
                    $ret_x['sum_tax'] += $item['sum_tax'];
                }
                unset($lines[$key]);
            }
        }

        return $ret_x['sum'] > 0 ? [$ret_x] : false;
    }


    /**
     * Есть ли комиссия в строках счета
     *
     * @param array $L
     * @return bool
     */
    public static function isAgentCommisionInLines($L)
    {
        foreach ($L as $line) {
            preg_match('/агентское вознагражден/ui', $line['item'], $agentMatch);
            if (isset($agentMatch[0])) {
                return true;
            }
        }
        return false;
    }

}