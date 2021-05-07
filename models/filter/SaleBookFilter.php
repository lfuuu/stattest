<?php

namespace app\models\filter;


use app\exceptions\web\NotImplementedHttpException;
use app\helpers\DateTimeZoneHelper;
use app\models\BusinessProcessStatus;
use app\models\Currency;
use app\models\Invoice;
use app\models\Organization;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

class SaleBookFilter extends Invoice
{
    const FILTER_ALL = 'all';
    const FILTER_NORMAL = 'normal';
    const FILTER_REVERSAL = 'reversal';
    const FILTER_ADDITION = 'dop_list';

    public static $filters = [
        self::FILTER_ALL => 'Всё',
        self::FILTER_NORMAL => 'Нормальные с/ф',
        self::FILTER_REVERSAL => 'Сторнированные',
        self::FILTER_ADDITION => '?Доп.лист',
    ];

    public static $skipping_bps = [
        BusinessProcessStatus::TELEKOM_MAINTENANCE_TRASH,
        BusinessProcessStatus::TELEKOM_MAINTENANCE_FAILURE,
        BusinessProcessStatus::WELLTIME_MAINTENANCE_FAILURE
    ];


    public
        $date_from = null,
        $date_to = null,
        $organization_id = null,
        /** \DateTimeImmutable */
        $dateFrom = null,
        /** \DateTimeImmutable */
        $dateTo = null,
        $filter = self::FILTER_NORMAL,
        $currency = Currency::RUB,
        $is_euro_format = 0;


    public function __construct()
    {
        $from = (new \DateTimeImmutable())->modify('first day of this month');

        $this->date_from = $from->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE);
        $this->date_to = $from->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE);
    }


    public function rules()
    {
        return [
            [['date_from', 'date_to', 'organization_id', /*'filter', */'currency'], 'required'],
            ['is_euro_format', 'integer'],
            [['date_from', 'date_to'], 'date'],
            [['organization_id'], 'in', 'range' => array_keys(Organization::dao()->getList())],
//            ['filter', 'in', 'range' => array_keys(self::$filters)],
        ];
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'is_euro_format' => 'ЕвроФормат',
            ];
    }

    public function beforeValidate()
    {
        if ($this->date_from && preg_match("/(\d{2})-(\d{2})-(\d{4})/", $this->date_from, $o)) {
            $this->dateFrom = (new \DateTimeImmutable())->setDate($o[3], $o[2], $o[1]);
        }

        if ($this->date_to && preg_match("/(\d{2})-(\d{2})-(\d{4})/", $this->date_to, $o)) {
            $this->dateTo = (new \DateTimeImmutable())->setDate($o[3], $o[2], $o[1]);
        }
    }


    /**
     * @return ActiveQuery
     * @throws NotSupportedException
     */
    public function search()
    {
        if (!$this->dateFrom || !$this->dateTo) {
            return false;
        }

        $query = self::find()
            ->alias('inv')
            ->where([
                'inv.organization_id' => $this->organization_id,
            ])
            ->andWhere(['between',
                        $this->is_euro_format ? 'bill.bill_date' : 'inv.date',
                        $this->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT),
                        $this->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
            ])
            ->andWhere(['NOT', ['number' => null]])
            ->orderBy([
                'inv.idx' => SORT_ASC,
                'inv.id' => SORT_ASC,
            ]);

        $query->joinWith('bill bill', true, 'INNER JOIN');
        $query->with('bill');

        $this->currency && $query->andWhere(['bill.currency' => $this->currency]);

        /*
        switch ($this->filter) {
            case self::FILTER_ALL:
                // nothing
                break;

            case self::FILTER_NORMAL:
                $query->andWhere(['is_reversal' => 0]);
                break;

            case self::FILTER_REVERSAL:
                $query->andWhere(['is_reversal' => 1]);
                break;

            default:
                throw new NotSupportedException('Не готово');
        }
        */

        return $query;
    }

    public function getPaymentsStr()
    {
        $str = "";

        foreach ($this->bill->payments as $payment) {
            $str && $str .= ', ';

            $str .= $payment->payment_no . '; ' .
                (new \DateTimeImmutable($payment->payment_date))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
        }

        return $str;
    }

    /**
     * Нужная счет-фактура или нет
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function check(Invoice $invoice)
    {
        $contract = $invoice->bill->clientAccount->contract;

        // internal office
        if ($contract->contract_type_id == 6) {
            return false;

        }

        // если есть с/ф-3 - значит была реализация
        if ($invoice->type_id == Invoice::TYPE_GOOD) {
            return true;
        }


        # AND IF(B.`sum` < 0, cr.`contract_type_id` =2, true) ### only telekom clients with negative sum

        # AND cr.`contract_type_id` != 6 ## internal office
        # AND cr.`business_process_status_id` NOT IN (22, 28, 99) ## trash, cancel

        return !(in_array($contract->business_process_status_id, self::$skipping_bps));
    }

}