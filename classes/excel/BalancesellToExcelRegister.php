<?php

namespace app\classes\excel;

use app\helpers\DateTimeZoneHelper;
use app\models\filter\SaleBookFilter;
use app\models\Invoice;
use DateTime;
use app\models\Organization;
use app\modules\uu\models\ServiceType;
use DateTimeImmutable;
use PHPExcel_RichText;

/** @var SaleBookFilter $filter */
class BalancesellToExcelRegister extends Excel
{

    private
        $insertPosition = 18;

    public
        /** @var \app\models\Organization $organization */
        $organization,
        $dateFrom,
        $dateTo,
        $filter,
        $skipping_bps;


    public function init()
    {
        $this->openFile(\Yii::getAlias('@app/templates/balance_sell_reg.xls'));

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

            $contragent = $contract->contragent;

            $sum16 = 0;
            foreach($invoice->lines as $line) {
                if (!($line->date_to <= (new DateTimeImmutable($this->dateTo))->format(DateTimeZoneHelper::DATE_FORMAT) && $line->date_from >= (new DateTimeImmutable($this->dateFrom))->format(DateTimeZoneHelper::DATE_FORMAT))){
                    continue;
                }
                if ($line->line->id_service) {
                    if ($line->line->accountTariff->service_type_id != ServiceType::ID_VPBX) {
                        continue;
                    }
                }
                $sum16 += abs($line['sum_tax']) > 0  ? 0 : $line['sum'];
            }

            if (abs($sum16) < 0.05) {
                continue;
            }

            $contract =  $invoice->bill->clientAccount->contract;
            $data[] = [
                'company_full' => trim($contragent->name_full),
                'inn' => trim($contragent->inn),
                'kpp' => trim($contragent->kpp),
                'inv_no' => $invoice->number,
                'inv_date' => $invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED), 
                'sum16' => $sum16,
                'contract_number' => $contract->number,
                'contract_date' => '01.09.2021',
            ];
        }
        return $data;
    }


    public function prepare(array $data)
    {
        /** @var \PHPExcel_Worksheet $worksheet */
        $worksheet = $this->document->getActiveSheet();

        $data = array_values($data);
        $worksheet->insertNewRowBefore($this->insertPosition, (count($data)-1) * 2);
        
        $this->setCompanyName($worksheet);
        $this->setYear($worksheet);
        $this->setInnKpp($worksheet);
        $this->setReorganizationForm($worksheet);
        $this->setReorganizationOrganization($worksheet);
        $this->setTaxpayer($worksheet);
        $this->setTaxPeriod($worksheet);
        $this->setFileName($worksheet);
        $this->setCorrectionNumber($worksheet);

        for ($i = 0, $t = count($data), $j = 0; $i < $t * 2; $i++) {
            $row = $data[$j];
            $line = $i + $this->insertPosition - 1;
            $companyName = str_replace(['«', '»'], '"', html_entity_decode($row['company_full']));
            $sum16 = sprintf('%0.2f', round($row['sum16'], 2));
            $worksheet->mergeCellsByColumnAndRow(3, $line, 3, $line + 1);
            $worksheet->setCellValueByColumnAndRow(3, $line, $companyName);

            $worksheet->mergeCellsByColumnAndRow(4, $line, 4, $line + 1);
            $worksheet->setCellValueByColumnAndRow(4, $line, $row['inn']);

            $worksheet->mergeCellsByColumnAndRow(5, $line, 5, $line + 1);
            $worksheet->setCellValueByColumnAndRow(5, $line, $row['kpp']);

            $worksheet->mergeCellsByColumnAndRow(9, $line, 9, $line + 1);
            $worksheet->setCellValueByColumnAndRow(9, $line, $sum16);

            $worksheet->setCellValueByColumnAndRow(6, $line, 'Лицензионное соглашение');
            $worksheet->setCellValueByColumnAndRow(6, $line+1, 'Акт');

            $worksheet->setCellValueByColumnAndRow(7, $line, $row['contract_number']);
            $worksheet->setCellValueByColumnAndRow(7, $line+1, $row['inv_no']);
            $worksheet->setCellValueByColumnAndRow(8, $line, $row['contract_date']);
            $worksheet->setCellValueByColumnAndRow(8, $line+1, $row['inv_date']);
            $i++;
            $j++;
        }
    }

    private function setCompanyName(\PHPExcel_Worksheet $worksheet)
    {
        if (!($this->organization instanceof Organization)) {
            return false;
        }  
        $worksheet->getStyle('A9')->getFont()->setSize('9');
        $worksheet->getStyle('A9')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('Наименование / фамилия, имя, отчество налогоплательщика ');

        $objBold = $objRichText->createTextRun('                        ' . $this->organization->name . '                        ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A9')->setValue($objRichText);
    }

    private function setYear(\PHPExcel_Worksheet $worksheet)
    {
        if (!($this->organization instanceof Organization)) {
            return false;
        }  
        $worksheet->getStyle('A5')->getFont()->setSize('9');
        $worksheet->getStyle('A5')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('Отчетный год: ');

        $objBold = $objRichText->createTextRun('                        ' . (new DateTimeImmutable('now'))->format('Y') . '                        ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A5')->setValue($objRichText);
    }

    private function setInnKpp(\PHPExcel_Worksheet $worksheet)
    {
        if (!($this->organization instanceof Organization)) {
            return false;
        }  
        $worksheet->getStyle('A8')->getFont()->setSize('9');
        $worksheet->getStyle('A8')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('ИНН ');

        $objBold = $objRichText->createTextRun('       ' . $this->organization->tax_registration_id . '       ');
        $objBold->getFont()->setUnderline(true);
        $objRichText->createText(' КПП: ');
        $objBold = $objRichText->createTextRun('       ' . $this->organization->tax_registration_reason . '       ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A8')->setValue($objRichText);
    }

    private function setTaxPeriod(\PHPExcel_Worksheet $worksheet)
    {
        $worksheet->getStyle('A4')->getFont()->setSize('9');
        $worksheet->getStyle('A4')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('Налоговый период (код): ');

        $objBold = $objRichText->createTextRun('       ' . '0' . '       ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A4')->setValue($objRichText);
    }

    private function setCorrectionNumber(\PHPExcel_Worksheet $worksheet)
    {
        $worksheet->getStyle('A6')->getFont()->setSize('9');
        $worksheet->getStyle('A6')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('Номер корректировки: ');

        $objBold = $objRichText->createTextRun('       ' . '0' . '       ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A6')->setValue($objRichText);
    }

    private function setTaxpayer(\PHPExcel_Worksheet $worksheet)
    {
        if (!($this->organization instanceof Organization)) {
            return false;
        }  
        $worksheet->getStyle('A7')->getFont()->setSize('9');
        $worksheet->getStyle('A7')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('Налогоплательщик');

        $worksheet->getCell('A7')->setValue($objRichText);
    }

    private function setReorganizationForm(\PHPExcel_Worksheet $worksheet)
    {
        $worksheet->getStyle('A10')->getFont()->setSize('9');
        $worksheet->getStyle('A10')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('Форма реорганизации (ликвидация) (код): ');

        $objBold = $objRichText->createTextRun('              ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A10')->setValue($objRichText);
    }

    private function setReorganizationOrganization(\PHPExcel_Worksheet $worksheet)
    {
        if (!($this->organization instanceof Organization)) {
            return false;
        }  
        $worksheet->getStyle('A11')->getFont()->setSize('9');
        $worksheet->getStyle('A11')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('ИНН/КПП реорганизованной организации: ');

        $objBold = $objRichText->createTextRun('                                      ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A11')->setValue($objRichText);
    }

    private function setFileName(\PHPExcel_Worksheet $worksheet)
    {
        $worksheet->getStyle('A12')->getFont()->setSize('9');
        $worksheet->getStyle('A12')->getFont()->setName('arial');  
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('Имя файла требования о представлении пояснений: ');

        $objBold = $objRichText->createTextRun('                               ' . '0000' . '                               ');
        $objBold->getFont()->setUnderline(true);

        $worksheet->getCell('A12')->setValue($objRichText);
    }  
}
