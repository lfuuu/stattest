<?php

namespace app\classes\operators;

use yii\base\Object;
use app\dao\reports\ReportExtendsOperatorsDao;

abstract class Operators extends Object
{

    protected static
        $requestProducts = [],
        $requestModes = [],
        $reportFields = [],
        $availableRequestStatuses = [];

    public function getOperator()
    {
        return 'onlime';
    }

    public function getOperatorClient()
    {
        return static::OPERATOR_CLIENT;
    }

    public function getReport()
    {
        return ReportExtendsOperatorsDao::me()->setOperator($this);
    }

    public function getRequestModes()
    {
        return static::$requestModes;
    }

    public function getProducts()
    {
        return static::$requestProducts;
    }

    public function getProductById($id)
    {
        foreach ($this->products as $product) {
            if ($id == $product['id'])
                return $product;
        }
    }

    public function getAvailableRequestStatuses()
    {
        return static::$availableRequestStatuses;
    }

    public function generateExcel($head, $list)
    {
        $objPHPExcel = new \PHPExcel;
        $objPHPExcel->setActiveSheetIndex(0);

        $sheet = $objPHPExcel->getActiveSheet();

        foreach ([10, 12, 21, 11, 29, 35, 33, 14, 14, 88] as $columnIndex => $width) {
            $sheet->getColumnDimensionByColumn($columnIndex + 1)->setWidth($width);
        }

        $idx = 0;
        foreach ($head as $title => $field) {
            if ($field == 'products') {
                foreach (static::$requestProducts as $product) {
                    $sheet->setCellValueByColumnAndRow($idx++, 2, $product['name']);
                }
            }
            else {
                $sheet->setCellValueByColumnAndRow($idx++, 2, $title);
            }
        }

        foreach ($list as $rowIdx => $item) {
            $colIdx = 0;
            foreach($head as $title => $field) {
                if ($field == 'products') {
                    foreach (static::$requestProducts as $i => $product) {
                        $sheet->setCellValueByColumnAndRow(
                            $colIdx++,
                            3 + $rowIdx,
                            isset($item['group_' . ($i + 1)]) ? strip_tags($item['group_' . ($i + 1)]) : ''
                        );
                    }
                }
                else if ($field == 'stages_text') {
                    $last_stage = array_pop($item['stages']);

                    $sheet->setCellValueByColumnAndRow(
                        $colIdx++,
                        3 + $rowIdx,
                        $last_stage['date_finish_desired'] . "\n" .
                        $last_stage['state_name'] . "\n" .
                        $last_stage['user_edit'] . "\n" .
                        $last_stage['comment']
                    );
                }
                else {
                    $sheet->setCellValueByColumnAndRow(
                        $colIdx++,
                        3 + $rowIdx,
                        isset($item[$field]) ? strip_tags($item[$field]) : ''
                    );
                }
            }
        }

        $oeWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        ob_start();
        $oeWriter->save('php://output');
        $content = ob_get_contents();
        ob_clean();

        return $content;
    }

}