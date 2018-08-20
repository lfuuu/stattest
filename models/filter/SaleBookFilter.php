<?php

namespace app\models\filter;


use app\exceptions\web\NotImplementedHttpException;
use app\helpers\DateTimeZoneHelper;
use app\models\Invoice;
use app\models\Organization;

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

    public
        $date_from = null,
        $date_to = null,
        $organization_id = null,
        /** \DateTimeImmutable */
        $dateFrom = null,
        /** \DateTimeImmutable */
        $dateTo = null,
        $filter = self::FILTER_NORMAL;


    public function __construct()
    {
        $from = (new \DateTimeImmutable())->modify('first day of this month');

        $this->date_from = $from->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE);
        $this->date_to = $from->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE);
    }


    public function rules()
    {
        return [
            [['date_from', 'date_to', 'organization_id','filter'], 'required'],
            [['date_from', 'date_to'], 'date'],
            [['organization_id'], 'in', 'range' => array_keys(Organization::dao()->getList())],
            ['filter', 'in', 'range' => array_keys(self::$filters)],
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


    public function search()
    {
        if (!$this->dateFrom || !$this->dateTo) {
            return false;
        }

        $query = self::find()
            ->where(['organization_id' => $this->organization_id])
            ->andWhere([
                'between',
                'date',
                $this->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT),
                $this->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
            ])
            ->orderBy([
                'date' => SORT_ASC,
                'id' => SORT_ASC,
            ]);

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
                throw new NotImplementedHttpException('Не готово');

        }

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

}