<?php

namespace app\classes\excel;

class OnlimeOperatorToExcel extends Excel
{

    private
        $insertColumnPosition = 4,
        $insertRowPosition = 4;

    public function prepare(array $fields, array $products, $report)
    {
        /** @var \PHPExcel_Worksheet $worksheet */
        $worksheet = $this->document->getActiveSheet();

        $worksheet->insertNewColumnBefore('F', count($products) - 1);
        $count = 0;
        foreach ($products as $product) {
            $colIdx = $count + $this->insertColumnPosition;
            $cell = $worksheet->setCellValueByColumnAndRow($colIdx, 3, $product['name'], true);
            $worksheet->mergeCells('E2:' . $cell->stringFromColumnIndex($colIdx) . '2');
            $count++;
        }
        $worksheet->getRowDimension(3)->setRowHeight(40);

        $worksheet->insertNewRowBefore($this->insertRowPosition + 1, count($report) - 1);
        foreach ($report as $rowIdx => $item) {
            $colIdx = 0;
            $rowIdx += $this->insertRowPosition;
            foreach($fields as $title => $field) {
                if ($field == 'products') {
                    foreach ($products as $key => $product) {
                        if (is_string($key)) {
                            $key = 'count_' . $key;
                        }
                        else {
                            $key = 'count_' . ($key + 1);
                        }
                        $worksheet->setCellValueByColumnAndRow(
                            $colIdx++,
                            $rowIdx,
                            isset($item[$key]) ? strip_tags($item[$key]) : ''
                        );
                    }
                }
                else if ($field == 'client') {
                    $worksheet->setCellValueByColumnAndRow(
                        $colIdx++,
                        $rowIdx,
                        $item['fio'] . "\n" . $item['phone'] . "\n" . $item['address']
                    );
                }
                else if ($field == 'stages_text') {
                    $last_stage = array_pop($item['stages']);

                    $worksheet->setCellValueByColumnAndRow(
                        $colIdx++,
                        $rowIdx,
                        $last_stage['date_finish_desired'] . "\n" . $last_stage['state_name'] . "\n" . $last_stage['user_edit'] . "\n" . $last_stage['comment']
                    );
                }
                else {
                    $worksheet->setCellValueByColumnAndRow(
                        $colIdx++,
                        $rowIdx,
                        isset($item[$field]) ? strip_tags($item[$field]) : ''
                    );
                }
                $worksheet->getRowDimension($rowIdx)->setRowHeight(40);
            }
        }
    }

}