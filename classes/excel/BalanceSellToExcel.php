<?php

namespace app\classes\excel;

use DateTime;
use app\models\Organization;

class BalanceSellToExcel extends Excel
{

    private
        $insertPosition = 13;
    public
        /** @var \app\models\Organization $organization */
        $organization,
        $dateFrom,
        $dateTo;

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
            $worksheet->setCellValueByColumnAndRow(1, $line, '01');
            $worksheet->setCellValueByColumnAndRow(2, $line, $row['inv_no'] . ';' . date('d.m.Y', $row['inv_date']));
            $worksheet->setCellValueByColumnAndRow(6, $line, $companyName);
            $worksheet->setCellValueByColumnAndRow(7, $line, $row['inn'] . ($row['type'] == 'org' ? '/' . ($row['kpp'] ?: '') : ''));
            $worksheet->setCellValueByColumnAndRow(13, $line, sprintf('%0.2f', round($row['sum'], 2)));
            $worksheet->setCellValueByColumnAndRow(14, $line, sprintf('%0.2f', round($row['sum_without_tax'], 2)));
            $worksheet->setCellValueByColumnAndRow(17, $line, sprintf('%0.2f', round($row['sum_tax'], 2)));
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