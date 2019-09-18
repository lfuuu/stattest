<?php

namespace app\classes\excel;
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
        $rowsCounter = 10;
        $counter = 1;

        /** @var \PHPExcel_Worksheet $worksheet */
        $worksheet = $this->document->getActiveSheet();

        $worksheet->insertNewRowBefore($rowsCounter, count($this->data));

        $this->setOrganization($worksheet);
        $this->setDateRange($worksheet);

        foreach ($this->data as $chunk) {
            $worksheet->setCellValueByColumnAndRow(0, $rowsCounter, $counter);
            $worksheet->setCellValueByColumnAndRow(1, $rowsCounter, $chunk['bill_no']);
            $worksheet->setCellValueByColumnAndRow(2, $rowsCounter, $chunk['bill_date']);
            $worksheet->setCellValueByColumnAndRow(3, $rowsCounter, $chunk['id']);
            $worksheet->setCellValueByColumnAndRow(4, $rowsCounter, $chunk['number']);
            $worksheet->setCellValueByColumnAndRow(5, $rowsCounter, $chunk['ext_bill_no']);
            $worksheet->setCellValueByColumnAndRow(6, $rowsCounter, $chunk['ext_bill_date']);
            $worksheet->setCellValueByColumnAndRow(7, $rowsCounter, $chunk['ext_invoice_no']);
            $worksheet->setCellValueByColumnAndRow(8, $rowsCounter, $chunk['ext_invoice_date']);
            $worksheet->setCellValueByColumnAndRow(9, $rowsCounter, $chunk['ext_akt_no']);
            $worksheet->setCellValueByColumnAndRow(10, $rowsCounter, $chunk['ext_akt_date']);
            $worksheet->setCellValueByColumnAndRow(11, $rowsCounter, $chunk['name_full']);
            $worksheet->setCellValueByColumnAndRow(12, $rowsCounter, $chunk['inn']);
            $worksheet->setCellValueByColumnAndRow(13, $rowsCounter, $chunk['kpp']);
            $worksheet->setCellValueByColumnAndRow(14, $rowsCounter, $chunk['address_jur']);
            $worksheet->setCellValueByColumnAndRow(15, $rowsCounter, $chunk['bill_sum']);
            $worksheet->setCellValueByColumnAndRow(16, $rowsCounter, $chunk['sum_without_vat']);
            $worksheet->setCellValueByColumnAndRow(17, $rowsCounter, $chunk['vat']);
            $worksheet->setCellValueByColumnAndRow(18, $rowsCounter, $chunk['sum']);
            $worksheet->setCellValueByColumnAndRow(19, $rowsCounter, $chunk['currency']);

            ++$rowsCounter;
            ++$counter;
        }
    }

    public function prepareToIfns()
    {
        $rowsCounter = 10;
        $counter = 1;

        /** @var \PHPExcel_Worksheet $worksheet */
        $worksheet = $this->document->getActiveSheet();

        $worksheet->insertNewRowBefore($rowsCounter + 1, count($this->data));

        $this->setOrganization($worksheet);
        $this->setDateRange($worksheet);

        foreach ($this->data as $chunk) {
            $worksheet->setCellValueByColumnAndRow(0, $rowsCounter, $counter);
            $worksheet->setCellValueByColumnAndRow(2, $rowsCounter, $chunk['bill_no'] .';'.' '. $chunk['bill_date']);
            $worksheet->setCellValueByColumnAndRow(7, $rowsCounter, $chunk['ext_invoice_date']);
            $worksheet->setCellValueByColumnAndRow(8, $rowsCounter, $chunk['name_full']);
            $worksheet->setCellValueByColumnAndRow(9, $rowsCounter,
                $chunk['legal_type'] != 'person' ? $chunk['inn']. '/'. $chunk['kpp'] : '');
            $worksheet->setCellValueByColumnAndRow(14, $rowsCounter, $chunk['sum']);
            $worksheet->setCellValueByColumnAndRow(15, $rowsCounter, $chunk['vat']);

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

    private function setDateRange(\PHPExcel_Worksheet $worksheet)
    {
        $cell = $worksheet->getCell('A5');
        $value = $cell->getValue();

        if (!$this->dateFrom || !$this->dateTo) {
            throw new Exception('Не указан временной период');
        }

        $value = str_replace('{DateFrom}', (new DateTime($this->dateFrom))->format('d.m.Y'), $value);
        $value = str_replace('{DateTo}', (new DateTime($this->dateTo))->format('d.m.Y'), $value);

        $worksheet->setCellValue('A5', $value);
    }
}