<?php

namespace app\modules\nnp\media;

use app\modules\nnp\filter\NumberRangeImport;

class ImportServiceUploaded extends ImportService
{
    const EVENT = 'nnp_import';

    private $_url;

    /**
     * Импортировать
     *
     * @param string $url
     * @return bool
     */
    public function run($url)
    {
        $this->delimiter = ';';
        $this->_url = $url;
        return parent::run();
    }

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
        $this->importFromTxt($this->_url);
    }

    /**
     * Преобразовать строчку файла в фиксированный массив данных
     *
     * @param int $i Номер строки
     * @param string[] $row ячейки строки csv-файла
     * @return string[] ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number']
     * @throws \RuntimeException
     */
    protected function callbackRow($i, $row)
    {
        if (!$i && !is_numeric($row[0])) {
            // Шапка (первая строчка с названиями полей) - пропустить
            return [];
        }

        return $this->getNumberRangeByRow($row)
            ->getSqlData();
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
        $numberRangeImport->setNdc($row[1]);
        $numberRangeImport->setNdcTypeSource($row[2]);
        $numberRangeImport->setNdcTypeId($row[3], $this->ndcTypeList);
        $numberRangeImport->setNumberFrom($row[4]);
        $numberRangeImport->setNumberTo($row[5]);
        $numberRangeImport->setRegionSource($row[6]);
        $numberRangeImport->setOperatorSource($row[7]);
        $numberRangeImport->setDateResolution($row[8]);
        $numberRangeImport->setDetailResolution($row[9]);
        $numberRangeImport->setStatusNumber($row[10]);

        return $numberRangeImport;
    }

    /**
     * @param NumberRangeImport $numberRangeImport
     * @return bool[] индексы соответствуют $row из getNumberRangeByRow. Значение - hasError
     */
    public function getRowHasError($numberRangeImport)
    {
        return [
            $numberRangeImport->hasErrors('country_prefix'),
            $numberRangeImport->hasErrors('ndc'),
            $numberRangeImport->hasErrors('ndc_type_source'),
            $numberRangeImport->hasErrors('ndc_type_id'),
            $numberRangeImport->hasErrors('number_from'),
            $numberRangeImport->hasErrors('number_to'),
            $numberRangeImport->hasErrors('region_source'),
            $numberRangeImport->hasErrors('operator_source'),
            $numberRangeImport->hasErrors('date_resolution'),
            $numberRangeImport->hasErrors('detail_resolution'),
            $numberRangeImport->hasErrors('status_number'),
        ];
    }
}