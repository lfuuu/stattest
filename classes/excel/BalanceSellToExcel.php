<?php

namespace app\classes\excel;

use app\helpers\DateTimeZoneHelper;
use app\models\filter\SaleBookFilter;
use app\models\Invoice;
use DateTime;
use app\models\Organization;

/** @var SaleBookFilter $filter */
class BalanceSellToExcel extends Excel
{

    private
        $insertPosition = 12;

    public
        /** @var \app\models\Organization $organization */
        $organization,
        $dateFrom,
        $dateTo,
        $filter,
        $skipping_bps;


    public function init()
    {
        $this->openFile(\Yii::getAlias('@app/templates/balance_sell.xls'));

        $this->organization = Organization::find()
            ->byId($this->filter->organization_id)
            ->actual()
            ->one();
        $this->dateFrom = $this->filter->date_from;
        $this->dateTo = $this->filter->date_to;

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
            $account = $invoice->bill->clientAccount;
            $contract = $account->contract;
            $currencyModel = $account->currencyModel;

            $contragent = $contract->contragent;
            $currencyId = $currencyModel->id;
            $currencyName = $currencyModel->name;
            $currencyCode = $currencyModel->code;
            $taxRate = $account->getTaxRate();
            $paymentsStr = $invoice->getPaymentsStr();

            $sumTax = 0;
            foreach($invoice->lines as $line) {
                $sumTax += abs($line['sum_tax']) > 0 ? $line['sum_without_tax'] : 0;
            }

            $data[] = [
                'code' => $invoice->type_id == Invoice::TYPE_PREPAID ? '02' : '01',
                'sum' => $invoice->sum,
                'sum_without_tax' => $invoice->type_id !== Invoice::TYPE_PREPAID ? $invoice->sum_without_tax : null,
                'sum_tax' => $invoice->sum_tax,
                'company_full' => trim($contragent->name_full),
                'inn' => trim($contragent->inn),
                'kpp' => trim($contragent->kpp),
                'inv_no' => $invoice->number . '; ' . $invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                'type' => $contragent->legal_type,
                'correction' => ($invoice->correction_idx ? $invoice->correction_idx . '; ' . $invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) : ''),
                'taxRate' => $taxRate,
                'currency_id' => $currencyId,
                'currency_name' => $currencyName,
                'currency_code' => $currencyCode,
                'payments_str' => $paymentsStr,
                'sumTax' => $sumTax,
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

        $this->setCompanyName($worksheet);
        $this->setTaxRegistration($worksheet);
        $this->setDateRange($worksheet);

        for ($i = 0, $t = count($data); $i < $t; $i++) {
            $row = $data[$i];

            $line = $i + $this->insertPosition - 1;
            $companyName = str_replace(['«', '»'], '"', html_entity_decode($row['company_full']));

            $worksheet->setCellValueByColumnAndRow(0, $line, ($i + 1));
            $worksheet->setCellValueByColumnAndRow(1, $line, $row['code']);
            $worksheet->setCellValueByColumnAndRow(2, $line, $row['inv_no']);
            $worksheet->setCellValueByColumnAndRow(5, $line, $row['correction']);
            $worksheet->setCellValueByColumnAndRow(8, $line, $companyName);
            $worksheet->setCellValueByColumnAndRow(9, $line,
                $row['inn'] . ($row['type'] == 'legal' ? '/' . ($row['kpp'] ?: '') : ''));
            $worksheet->setCellValueByColumnAndRow(12, $line, $row['payments_str']);
            $worksheet->setCellValueByColumnAndRow(13, $line,
                $row['currency_id'] == 'RUB' ? ' ' : $row['currency_name'] .' '. $row['currency_code']);
            $worksheet->setCellValueByColumnAndRow(14, $line,
                $row['currency_id'] == 'RUB' ? '' :
                        sprintf('%0.2f', round($row['sum'], 2)));
            $worksheet->setCellValueByColumnAndRow(15, $line, sprintf('%0.2f', round($row['sum'], 2)));
            $worksheet->setCellValueByColumnAndRow(16, $line,
                $row['sum_without_tax'] !== null && $row['taxRate'] == 20 ? sprintf('%0.2f', round($row['sumTax'], 2)) : '');
            $worksheet->setCellValueByColumnAndRow(17, $line,
                $row['taxRate'] == 18 ? sprintf('%0.2f', round($row['sum_without_tax'], 2)) : '');
            $worksheet->setCellValueByColumnAndRow(18, $line,
                $row['taxRate'] == 10 ? sprintf('%0.2f', round($row['sum_without_tax'], 2)) : '');
            $worksheet->setCellValueByColumnAndRow(19, $line,
                $row['taxRate'] == 0 ? sprintf('%0.2f', round($row['sum_without_tax'], 2)) : '');
            $worksheet->setCellValueByColumnAndRow(20, $line,
                $row['taxRate'] == 20 ? sprintf('%0.2f', round($row['sum_tax'], 2)) : '');
            $worksheet->setCellValueByColumnAndRow(21, $line,
                $row['taxRate'] == 18 ? sprintf('%0.2f', round($row['sum_tax'], 2)) : '');
            $worksheet->setCellValueByColumnAndRow(22, $line,
                $row['taxRate'] == 10 ? sprintf('%0.2f', round($row['sum_tax'], 2)) : '');
        }
    }

    private function setCompanyName(\PHPExcel_Worksheet $worksheet)
    {
        if (!($this->organization instanceof Organization)) {
            return false;
        }

        $cell = $worksheet->getCell('A4');
        $value = str_replace('{Name}', $this->organization->name, $cell->getValue());
        $worksheet->setCellValue('A4', $value);
    }

    private function setTaxRegistration(\PHPExcel_Worksheet $worksheet)
    {
        if (!($this->organization instanceof Organization)) {
            return false;
        }

        $cell = $worksheet->getCell('A5');
        $value =
            str_replace(
                '{InnKpp}',
                $this->organization->tax_registration_id .
                (
                $this->organization->tax_registration_reason
                    ? '/' . $this->organization->tax_registration_reason
                    : ''
                ),
                $cell->getValue()
            );
        $worksheet->setCellValue('A5', $value);
    }

    private function setDateRange(\PHPExcel_Worksheet $worksheet)
    {
        $cell = $worksheet->getCell('A6');
        $value = $cell->getValue();

        if ($this->dateFrom) {
            $value = str_replace('{DateFrom}', (new DateTime($this->dateFrom))->format('d.m.Y'), $value);
        }

        if ($this->dateTo) {
            $value = str_replace('{DateTo}', (new DateTime($this->dateTo))->format('d.m.Y'), $value);
        }

        $worksheet->setCellValue('A6', $value);
    }

}
