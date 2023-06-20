<?php

namespace app\classes\excel;
use app\helpers\DateTimeZoneHelper;
use app\models\Currency;
use app\models\Organization;
use DateTime;
use yii\base\Exception;


class PurchaseBookToExcelBmd extends Excel
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
            $worksheet->setCellValueByColumnAndRow(1, $rowsCounter, 0);
            $worksheet->setCellValueByColumnAndRow(2, $rowsCounter, 'ER');
            $worksheet->setCellValueByColumnAndRow(3, $rowsCounter, 2);
            $worksheet->setCellValueByColumnAndRow(4, $rowsCounter, 'E');
            $worksheet->setCellValueByColumnAndRow(5, $rowsCounter, 'A');
            $worksheet->setCellValueByColumnAndRow(6, $rowsCounter, (string)$chunk['account_id']);
            $worksheet->setCellValueByColumnAndRow(7, $rowsCounter, (new DateTime($chunk['invoice_date']))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED));
            $worksheet->setCellValueByColumnAndRow(8, $rowsCounter, (new DateTime($chunk['due_date']))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED));
            $worksheet->setCellValueByColumnAndRow(9, $rowsCounter, (string)$chunk['ext_invoice_no']);
            $worksheet->setCellValueByColumnAndRow(10, $rowsCounter, (string)$chunk['currency']);
            $worksheet->setCellValueByColumnAndRow(11, $rowsCounter, $chunk['bmd']['fwbetrag']);
            $worksheet->setCellValueByColumnAndRow(12, $rowsCounter, $chunk['bmd']['betrag']);
            $worksheet->setCellValueByColumnAndRow(13, $rowsCounter, $chunk['bmd']['fwsteuer']);
            $worksheet->setCellValueByColumnAndRow(14, $rowsCounter, $chunk['bmd']['steuer']);
            $worksheet->setCellValueByColumnAndRow(15, $rowsCounter, $chunk['bmd']['prozent']);
            $worksheet->setCellValueByColumnAndRow(16, $rowsCounter, $chunk['bmd']['steuercode']);
            $worksheet->setCellValueByColumnAndRow(17, $rowsCounter, $chunk['bmd']['gkonto']);
            $worksheet->setCellValueByColumnAndRow(18, $rowsCounter, 'See STAT bill ' . $chunk['bill_no'] . ' for details');
            $worksheet->setCellValueByColumnAndRow(19, $rowsCounter, $chunk['file_name']);

            ++$rowsCounter;
            ++$counter;
        }
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