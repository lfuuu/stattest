<?php

namespace app\dao\reports\ReportUsage\Processor;

use app\dao\reports\ReportUsage\Helper;
use app\dao\reports\ReportUsage\Processor;

class StatisticsDestinations extends Processor
{
    // ************************************************************************
    // Overrides

    /**
     * Пре-обработка
     * @throws \Exception
     */
    public function processBefore()
    {
        $this->prepareDestinationQuery();
        $this->setDefaults();
    }

    /**
     * Обработчик записи
     *
     * @param array $item
     */
    public function processItem(array $item)
    {
        $this->processDestinationRecord($item);
    }

    /**
     * Пост-обработка
     */
    public function processAfter()
    {
        $this->addTotal();
    }


    // ************************************************************************
    // Customs

    /**
     *
     * @throws \Exception
     */
    protected function prepareDestinationQuery()
    {
        $query = $this->query = $this->initQuery();

        $query->select([
            'dest' => 'cr.destination_id',
            'cr.mob',
            'price' => '-SUM(cr.cost)',
            'len' => 'SUM(cr.billed_time)',
            'cnt' => 'SUM(1)'
        ]);

        if ($this->isWithProfit()) {
            $query->addSelect([
                'cost_price' =>
                    'SUM' .
                    '(CASE WHEN cr.orig THEN cr2.cost' . $this->rateCur2 . ' ELSE cr.cost' . $this->rateCur1 . ' END)',

                'price' =>
                    '-SUM' .
                    '(CASE WHEN cr.orig THEN cr.cost' . $this->rateCur1 . ' ELSE cr2.cost' . $this->rateCur2 . ' END)',

                'profit' =>
                    '-SUM' .
                    '(cr.cost' . $this->rateCur1 . ' + cr2.cost' . $this->rateCur2 . ')',
            ]);
        }

        $query->groupBy(['cr.destination_id', 'cr.mob']);
    }

    /**
     * Устанавливаем начальные значения
     */
    protected function setDefaults()
    {
        $destinations = [
            'mos_loc' => 'Местные Стационарные',
            'mos_mob' => 'Местные Мобильные',
            'rus_fix' => 'Россия Стационарные',
            'rus_mob' => 'Россия Мобильные',
            'int' => 'Международка',
        ];
        $emptyRow = [
            'tsf1' => 'Name',
            'cnt' => 0,
            'len' => 0,
            'price' => 0,
            'is_total' => false
        ];

        foreach ($destinations as $key => $destination) {
            $totalRow = $emptyRow;
            $totalRow['tsf1'] = $destination;

            if ($this->isWithProfit()) {
                $this->result[$key]['cost_price'] = 0;
                $this->result[$key]['profit'] = 0;
            }

            $this->result[$key] = $totalRow;
        }
    }

    /**
     * @param array $record
     */
    protected function processDestinationRecord(array $record)
    {
        $addRowToTotal = function ($record, $key) {
            $this->result[$key]['len'] += $record['len'];
            $this->result[$key]['cnt'] += $record['cnt'];
            $this->result[$key]['price'] += $record['price'];

            if ($this->isWithProfit()) {
                $this->result[$key]['cost_price'] += $record['cost_price'];
                $this->result[$key]['profit'] += $record['profit'];
            }
        };

        if ($record['dest'] <= 0 && $record['mob'] === false) {
            $addRowToTotal($record, 'mos_loc');
        } elseif ($record['dest'] <= 0 && $record['mob'] === true) {
            $addRowToTotal($record, 'mos_mob');
        } elseif ($record['dest'] == 1 && $record['mob'] === false) {
            $addRowToTotal($record, 'rus_fix');
        } elseif ($record['dest'] == 1 && $record['mob'] === true) {
            $addRowToTotal($record, 'rus_mob');
        } elseif ($record['dest'] == 2 || $record['dest'] == 3) {
            $addRowToTotal($record, 'int');
        }
    }

    /**
     * Добавление тотала
     */
    protected function addTotal()
    {
        $cnt = 0;
        $len = 0;
        $costPrice = 0;
        $price = 0;
        $profit = 0;
        foreach ($this->result as $destination => $data) {
            $cnt += $data['cnt'];
            $len += $data['len'];
            $price += $data['price'];
            if ($this->isWithProfit()) {
                $costPrice += $data['cost_price'];
                $profit += $data['profit'];
            }

            $delta = 0;
            if ($data['len'] >= 24 * 60 * 60) {
                $delta = floor($data['len'] / (24 * 60 * 60));
            }

            $this->result[$destination]['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s',
                    $data['len'] - $delta * 24 * 60 * 60);
            $this->result[$destination]['price'] = number_format($data['price'], 2, '.', '');
            if ($this->isWithProfit()) {
                $this->result[$destination]['cost_price'] = number_format($data['cost_price'], 2, '.', '');
                $this->result[$destination]['profit'] = number_format($data['profit'], 2, '.', '');
            }
        }

        $delta = 0;
        $totals = [
            'is_total' => true,
            'tsf1' => 'Итого'
        ];

        if ($len >= 24 * 60 * 60) {
            $delta = floor($len / (24 * 60 * 60));
        }

        $totals['tsf2'] = ($delta ? $delta . 'd ' : '') . gmdate('H:i:s', $len - $delta * 24 * 60 * 60);
        $totals['cnt'] = $cnt;

        $totals['price'] = $price;
        if ($this->isWithProfit()) {
            $totals['cost_price'] = $costPrice;
            $totals['profit'] = $profit;
        }
        $totals = Helper::calcRow($this->getAccount(), $totals, $this->isWithProfit());

        $this->result['total'] = $totals;
    }
}
