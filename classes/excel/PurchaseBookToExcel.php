<?php

namespace app\classes\excel;
use app\models\Currency;
use app\models\Organization;
use DateTime;
use yii\base\Exception;


class PurchaseBookToExcel extends Excel
{
    public $data;
    public $total;
    public $dateFrom;
    public $dateTo;
    public $organizationId;

    /**
     * @inheritdoc
     * @throws Exception
     * @throws \PHPExcel_Exception
     */
    public function prepare()
    {
        $rowsCounter = 2;
        $counter = 1;

        /** @var \PHPExcel_Worksheet $worksheet */
        $worksheet = $this->document->getActiveSheet();

        $worksheet->insertNewRowBefore($rowsCounter, count($this->data));

        $this->setOrganization($worksheet);
        $this->setDateRange($worksheet);

        foreach ($this->data as $chunk) {
            $worksheet->setCellValueByColumnAndRow(0, $rowsCounter, $counter);
            $worksheet->setCellValueByColumnAndRow(1, $rowsCounter, $chunk['bill_no']);
            $worksheet->setCellValueByColumnAndRow(2, $rowsCounter, (new DateTime($chunk['registration_date']))->format('F'));
            $worksheet->setCellValueByColumnAndRow(3, $rowsCounter, $chunk['account_id']);
            $worksheet->setCellValueByColumnAndRow(4, $rowsCounter, $chunk['name_full']);
            $worksheet->setCellValueByColumnAndRow(5, $rowsCounter, $chunk['country_name']);
            $worksheet->setCellValueByColumnAndRow(6, $rowsCounter, $chunk['inn']);
            $worksheet->setCellValueByColumnAndRow(7, $rowsCounter, $chunk['inn_euro']);
            $worksheet->setCellValueByColumnAndRow(8, $rowsCounter, $chunk['ext_invoice_no']);
            $worksheet->setCellValueByColumnAndRow(9, $rowsCounter, $chunk['invoice_date']);
            $worksheet->setCellValueByColumnAndRow(10, $rowsCounter, $chunk['due_date']);
            $worksheet->setCellValueByColumnAndRow(11, $rowsCounter, $chunk['sum_without_vat']);
            $worksheet->setCellValueByColumnAndRow(12, $rowsCounter, $chunk['vat']);
            $worksheet->setCellValueByColumnAndRow(13, $rowsCounter, $chunk['sum']);
            $worksheet->setCellValueByColumnAndRow(14, $rowsCounter, $chunk['currency']);
            $worksheet->setCellValueByColumnAndRow(15, $rowsCounter, $chunk['rate']);
            $worksheet->setCellValueByColumnAndRow(16, $rowsCounter, $chunk['sum_without_vat_euro']);
            $worksheet->setCellValueByColumnAndRow(17, $rowsCounter, $chunk['vat_euro']);
            $worksheet->setCellValueByColumnAndRow(18, $rowsCounter, $chunk['sum_euro']);
            $worksheet->setCellValueByColumnAndRow(19, $rowsCounter, '...');

            ++$rowsCounter;
            ++$counter;
        }
    }

    public function prepareToIfns()
    {
        $rowsCounter = 12;
        $counter = 1;

        /** @var \PHPExcel_Worksheet $worksheet */
        $worksheet = $this->document->getActiveSheet();

        $worksheet->insertNewRowBefore($rowsCounter + 1, count($this->data));

        $this->setOrganization($worksheet);
        $this->setDateRange($worksheet);
        $this->setInnKpp($worksheet);

        foreach ($this->data as $chunk) {
            $worksheet->setCellValueByColumnAndRow(0, $rowsCounter, $counter);
            $worksheet->setCellValueByColumnAndRow(1, $rowsCounter, '01');
            $worksheet->setCellValueByColumnAndRow(2, $rowsCounter, $chunk['bill_no'] .' от '. $chunk['ext_invoice_date']);
            $worksheet->setCellValueByColumnAndRow(3, $rowsCounter, $chunk['correction_number'] ? $chunk['correction_number'] . ' от ' . $chunk['correction_date'] : '');
            $worksheet->setCellValueByColumnAndRow(7, $rowsCounter, $chunk['ext_invoice_date']);
            $worksheet->setCellValueByColumnAndRow(8, $rowsCounter, $chunk['name_full']);
            $worksheet->setCellValueByColumnAndRow(9, $rowsCounter, $chunk['legal_type'] != 'person' ? $chunk['inn']. '/'. $chunk['kpp'] : '');
            $worksheet->setCellValueByColumnAndRow(12, $rowsCounter, $chunk['currency']);
            $worksheet->setCellValueByColumnAndRow(13, $rowsCounter, $chunk['sum']);
            $worksheet->setCellValueByColumnAndRow(14, $rowsCounter, $chunk['vat']);

            ++$rowsCounter;
            ++$counter;
        }
        $worksheet->removeRow($rowsCounter,1);
    }

    private function setOrganization(\PHPExcel_Worksheet $worksheet)
    {
        $cell = $worksheet->getCell('A4');
        $value = $cell->getValue();

        $organization = Organization::findOne($this->organizationId);
        $name = ($organization) ? $organization->name : '';
        $value = str_replace('{Name}', $name, $value);

        $worksheet->setCellValue('A4', $value);


    }

    private function setInnKpp(\PHPExcel_Worksheet $worksheet)
    {
        $cell = $worksheet->getCell('A5');
        $value = $cell->getValue();

        $organization = Organization::findOne($this->organizationId);
        $inn = ($organization) ? $organization->tax_registration_id : '';
        $kpp = ($organization) ? $organization->tax_registration_reason : '';

        $value = str_replace('{inn}', $inn, $value);
        $value = str_replace('{kpp}', $kpp, $value);
        $worksheet->setCellValue('A5', $value);
    }

    private function setDateRange(\PHPExcel_Worksheet $worksheet)
    {
        $cell = $worksheet->getCell('A6');
        $value = $cell->getValue();

        if (!$this->dateFrom || !$this->dateTo) {
            throw new Exception('Не указан временной период');
        }

        $value = str_replace('{DateFrom}', (new DateTime($this->dateFrom))->format('d.m.Y'), $value);
        $value = str_replace('{DateTo}', (new DateTime($this->dateTo))->format('d.m.Y'), $value);

        $worksheet->setCellValue('A6', $value);
    }
}