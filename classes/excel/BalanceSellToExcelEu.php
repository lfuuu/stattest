<?php

namespace app\classes\excel;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientContragent;
use app\models\filter\SaleBookFilter;
use DateTime;
use app\models\Organization;
use yii\helpers\Url;

/** @var SaleBookFilter $filter */
class BalanceSellToExcelEu extends Excel
{

    private
        $insertPosition = 3;

    public
        /** @var \app\models\Organization $organization */
        $organization,
        $dateFrom,
        $dateTo,
        $filter,
        $skipping_bps,
        $format;


    public function init()
    {
        $this->openFile(\Yii::getAlias('@app/templates/balance_sell_eu.xls'));

        $this->organization = Organization::find()
            ->byId($this->filter->organization_id)
            ->actual()
            ->one();
        $this->dateFrom = $this->filter->date_from;
        $this->dateTo = $this->filter->date_to;

        $this->format = [
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_NUMERIC,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING,
            \PHPExcel_Cell_DataType::TYPE_STRING
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

            if (!$this->filter->check($invoice)) {
                continue;
            }

            /** @var \app\models\filter\SaleBookFilter $invoice */
            $bill = $invoice->bill;
            $account = $bill->clientAccount;
            $contract = $account->contract;

            $contragent = $contract->contragent;

            $rate = $invoice->getCurrencyRateToEuro();

            $inEuro = [
                'rate' => $rate,
                'total' => $invoice->sum * $rate,
                'vat' => $invoice->sum_tax * $rate,
                'net' => $invoice->sum_without_tax * $rate,
            ];

            $data[] = [
                (new DateTime($invoice->date))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                $account->id,
                $contragent->country->name,
                $contragent->legal_type,
                trim($contragent->name_full),
                trim($contragent->legal_type == ClientContragent::PERSON_TYPE ? $contragent->person->registration_address : $contragent->address_jur),
                $contract->business->name . ' / ' . $contract->businessProcessStatus->name,
                $invoice->number,
                $invoice->is_reversal && $invoice->getReversalInvoice() ? $invoice->getReversalInvoice()->number : '',
                (new DateTime($bill->pay_bill_until))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                $invoice->sum,
                $invoice->bill->currency,
                $invoice->sum_tax,
                $invoice->sum_without_tax,
                $account->getTaxRate($bill->bill_date) . '%',

                $inEuro['rate'],
                $inEuro['net'],
                $inEuro['vat'],
                $inEuro['total'],

                $contragent->inn_euro,
                $contragent->inn,
                \Yii::$app->params['SITE_URL']. Url::to([
                    '',
                    'module' => 'newaccounts',
                    'action' => 'bill_mprint',
                    'bill' => $invoice->bill_no,
                    'invoice2' => $invoice->type_id,
                    'invoice_id' => $invoice->id
                ])
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
                $worksheet->setCellValueExplicitByColumnAndRow($idx, $line, $value, $this->format[$idx]);
            }
        }
    }

    private function _printSum($sum)
    {
        return str_replace(".", ",", sprintf("%0.2f", $sum));
    }

}
