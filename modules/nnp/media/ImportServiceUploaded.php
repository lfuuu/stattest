<?php

namespace app\modules\nnp\media;

class ImportServiceUploaded extends ImportService
{
    const EVENT = 'nnp_import';

    private $_url;

    /**
     * Импортировать
     *
     * @param int $countryCode
     * @param string $url
     * @return int
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function run($countryCode, $url)
    {
        $this->_url = $url;
        return parent::run($countryCode);
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
     */
    protected function callbackRow($i, $row)
    {
        if (!$i && !is_numeric($row[0])) {
            // Шапка (первая строчка с названиями полей) - пропустить
            return [];
        }

        // date_resolution
        $row[8] = trim($row[8]);
        if ($row[8]) {
            $row[8] = str_replace('.', '-', $row[8]); // ГГГГ.ММ.ДД преобразовать в ГГГГ-ММ-ДД. Остальные форматы strtotime распознает сам
            $row[8] = date('Y-m-d', strtotime($row[8]));
        }

        return
            [
                // @todo [2]
                $row[1] ? (int)$row[1] : null, // ndc
                (int)$row[4], // number_from
                (int)$row[5], // number_to
                (int)$row[3], // ndc_type_id
                trim($row[7]), // operator_source
                trim($row[6]), // region_source
                (int)$row[0] . (int)$row[1] . (int)$row[4], // full_number_from
                (int)$row[0] . (int)$row[1] . (int)$row[5], // full_number_to
                $row[8] ?: null, // date_resolution
                trim($row[9]), // detail_resolution
                trim($row[10]), // status_number
            ];
    }
}