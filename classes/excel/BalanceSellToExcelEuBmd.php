<?php

namespace app\classes\excel;

use app\helpers\DateTimeZoneHelper;
use app\models\Currency;
use app\models\filter\SaleBookFilter;
use DateTime;
use app\models\Organization;

class BalanceSellToExcelEuBmd extends Excel
{

    private
        $insertPosition = 3;

    public
        /** @var \app\models\Organization $organization */
        $organization,
        $dateFrom,
        $dateTo,

        /** @var SaleBookFilter $filter */
        $filter,
        $skipping_bps,
        $format;


    public function init()
    {
        $this->openFile(\Yii::getAlias('@app/templates/balance_sell_eu_bmd.xls'));

        $this->organization = Organization::find()
            ->byId($this->filter->organization_id)
            ->actual()
            ->one();
        $this->dateFrom = $this->filter->date_from;
        $this->dateTo = $this->filter->date_to;

        $this->format = [
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,

            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,

            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
        ];

        $data = $this->_dataConversionToStandard();

        $this->prepare($data);
    }


    /**
     * @return array
     */
    private function _dataConversionToStandard()
    {
        $data = [];
        foreach ($this->filter->search()->each() as $invoice) {

            /** @var \app\models\filter\SaleBookFilter $invoice */
            $bill = $invoice->bill;
            $account = $bill->clientAccount;
            $contract = $account->contract;

            $contragent = $contract->contragent;

            $invoiceDate = new DateTime($invoice->date);
            $invoiceDateQuarter = ceil($invoiceDate->format('m') / 3);

            $filePathAr = ['\\', 'tsclient', 'C', 'INVOICE',
                $invoiceDate->format('Y') . '_Q' . $invoiceDateQuarter,
                $invoice->getFileName()];

            $data[] = [
                0,
                'AR',
                1,
                'E',
                'A',
                $account->id,
                $invoiceDate->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                $invoiceDate->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                $invoice->number,
                $invoice->bill->currency,
                ($invoice->bill->currency != Currency::EUR && $invoice->sum != 0 ? $invoice->sum : ''),
                ($invoice->bill->currency == Currency::EUR && $invoice->sum != 0 ? $invoice->sum : ''),
                ($invoice->bill->currency != Currency::EUR && $invoice->sum_tax != 0 ? -$invoice->sum_tax : ''),
                ($invoice->bill->currency == Currency::EUR && $invoice->sum_tax != 0 ? -$invoice->sum_tax : ''),
                $account->getTaxRate(),
                $this->filter->getFilial($contragent),
                $this->filter->getSteuerCode($this->filter->getSteuer($contragent, $contract, $invoice, $account->getTaxRate(), 1)),
                $this->filter->getAtCode($contract, $contragent),
                'telekommunikationsdinstleitungen',
                implode('\\', $filePathAr),
            ];
        }
        return $data;
    }


    public function prepare(array $data)
    {
        /** @var \PHPExcel_Worksheet $worksheet */
        $worksheet = $this->document->getActiveSheet();

        $data = array_values($data);
        $worksheet->insertNewRowBefore($this->insertPosition, count($data) - 1);


        for ($i = 0, $t = count($data); $i < $t; $i++) {
            $row = $data[$i];

            $line = $i + $this->insertPosition - 1;
            foreach ($row as $idx => $value) {
                $value = str_replace(['«', '»'], '"', html_entity_decode($value));
                $worksheet->setCellValueExplicitByColumnAndRow($idx, $line, $value, $value === '' ? \PHPExcel_Cell_DataType::TYPE_STRING : $this->format[$idx]);
            }
        }
    }

    private function _printSum($sum)
    {
        return str_replace(".", ",", sprintf("%0.2f", $sum));
    }
}
