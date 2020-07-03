<?php

namespace app\modules\nnp2\media;

use app\modules\nnp2\filters\NumberRangeImport;

class ImportServiceUploadedNew extends ImportServiceNew
{
    public $url;

    /**
     * Основной метод
     * Вызывается после _pre и перед _post
     * Внутри себя должен вызвать _importFromTxt
     *
     * @throws \UnexpectedValueException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    protected function callbackMethod()
    {
        $this->readFromTxt($this->url);
        $this->importData();
    }

    /**
     * Преобразовать строчку файла в фиксированный массив данных
     *
     * @param int $i Номер строки
     * @param string[] $row ячейки строки csv-файла
     * @return string[] ['geo_place_id', 'ndc_type_id', 'operator_id', 'number_from', 'number_to', 'full_number_from', 'full_number_to', 'allocation_reason', 'allocation_date_start', 'comment',]
     * @throws \RuntimeException
     */
    protected function callbackRow($i, $row)
    {
        if (!$i && !is_numeric($row[0])) {
            // Шапка (первая строчка с названиями полей) - пропустить
            return [];
        }

        $nr = $this->getNumberRangeByRow($row);
        if ($errors = $nr->getErrors()) {
            $attrs = $nr->getAttributes();

            $realNdc = $this->ndcTypeRelated->getRealNdcTypeId($attrs['ndc_type_id']);
            if ($realNdc == 6) {
                return [];
            }

            $attrs[] = ($i+1) . ': ' . implode('/', array_keys($errors)) . sprintf(' (%s)', $realNdc);
            $this->errorRows[] = implode(', ', $attrs);


        }
        return $nr->getSqlData();
    }

    /**
     * @param string[] $row
     * @return NumberRangeImport
     */
    public function getNumberRangeByRow($row)
    {
        $row += array_fill(count($row), 11, null);

        $numberRangeImport = new NumberRangeImport;
        $numberRangeImport->setCountryPrefix($row[0], $this->country);

        $numberRangeImport->setGeoPlaceId($row[1], $row[6], $row[7], $this->geoRelated->getList());
        $numberRangeImport->setNdcTypeId($row[2], $this->ndcTypeRelated->getList());
        $numberRangeImport->setOperatorId($row[8], $this->operatorRelated->getList());

        $numberRangeImport->setNumberFrom($row[4]);
        $numberRangeImport->setNumberTo($row[5]);

        $numberRangeImport->setAllocationDateStart($row[9]);
        $numberRangeImport->setAllocationReason($row[10]);
        $numberRangeImport->setComment($row[11]);

        return $numberRangeImport;
    }

    /**
     * @param NumberRangeImport $numberRangeImport
     * @return bool[] индексы соответствуют $row из getNumberRangeByRow. Значение - hasError
     */
    public function getRowHasError($numberRangeImport)
    {
        return [
            $numberRangeImport->hasErrors('geo_place_id'),
            $numberRangeImport->hasErrors('ndc_type_id'),
            $numberRangeImport->hasErrors('operator_id'),
            $numberRangeImport->hasErrors('number_from'),
            $numberRangeImport->hasErrors('number_to'),
            $numberRangeImport->hasErrors('full_number_from'),
            $numberRangeImport->hasErrors('full_number_to'),
            $numberRangeImport->hasErrors('allocation_date_start'),
            $numberRangeImport->hasErrors('allocation_reason'),
            $numberRangeImport->hasErrors('comment'),
        ];
    }
}