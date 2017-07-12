<?php

namespace app\modules\nnp\media;

use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;

class ImportServiceRossvyaz extends ImportService
{

    private $_ndcTypeId;

    /**
     * Импортировать
     *
     * @return bool
     */
    public function run()
    {
        $this->delimiter = ';';
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
        /**
         * Ссылки на файлы для скачивания
         * [url => ndc_type_id]
         *
         * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
         */
        $rusUrls = [
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-3kh.csv' => NdcType::ID_GEOGRAPHIC,
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-4kh.csv' => NdcType::ID_GEOGRAPHIC,
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-8kh.csv' => NdcType::ID_GEOGRAPHIC,
            'http://www.rossvyaz.ru/docs/articles/Kody_DEF-9kh.csv' => NdcType::ID_MOBILE,
        ];
        foreach ($rusUrls as $url => $ndcTypeId) {
            $this->_ndcTypeId = $ndcTypeId;
            $this->importFromTxt($url);
        }
    }

    /**
     * Преобразовать строчку файла в фиксированный массив данных
     *
     * @param int $i Номер строки
     * @param string[] $row ячейки строки csv-файла
     * @return string[] ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number', 'ndc_type_source']
     */
    protected function callbackRow($i, $row)
    {
        if (!$i) {
            // Шапка (первая строчка с названиями полей) - пропустить
            return [];
        }

        return
            [
                (int)$row[0], // ndc
                trim($row[1]), // number_from
                trim($row[2]), // number_to
                ($row[0] == 800) ? NdcType::ID_FREEPHONE : $this->_ndcTypeId, // ndc_type_id
                $this->_iconv($row[4]), // operator_source
                $this->_iconv($row[5]), // region_source
                Country::RUSSIA_PREFIX . ((int)$row[0]) . trim($row[1]), // full_number_from
                Country::RUSSIA_PREFIX . ((int)$row[0]) . trim($row[2]), // full_number_to
                null, // date_resolution
                null, // detail_resolution
                null, // status_number
                null, // ndc_type_source
            ];
    }

    /**
     * @param string $data
     * @return string
     */
    private function _iconv($data)
    {
        return trim(iconv("cp1251", "utf-8//TRANSLIT", $data));
    }

    /**
     * @param string $message
     */
    protected function addLog($message)
    {
        echo $message;
    }
}