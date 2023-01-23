<?php

namespace app\modules\nnp\commands;

use app\modules\nnp\classes\RouteMncDownloader;
use app\modules\nnp\models\Country;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\web\NotFoundHttpException;

/**
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=25887976
 *
 * router-17_01_2023.xls
 * Код DEF    От    До    Емкость (всего номеров)    Оператор связи    Идентификатор региона    MNC    Маршрутный номер
 * 900    0000000    0061999    62000    "Т2 Мобайл" ООО    25    20    D2520
 * 900    0062000    0062999    1000    "Т2 Мобайл" ООО    62    20    D6220
 * 900    0063000    0099999    37000    "Т2 Мобайл" ООО    25    20    D2520
 */
class PortedRussiaRouteMncController extends PortedController
{
    const SCHEMA = [
        'table' => 'nnp.route_mnc',
        'pk' => 'full_number_from',
        'fields' => [
            'ndc' => 'integer NOT NULL',
            'full_number_from' => 'bigint NOT NULL',
            'full_number_to' => 'bigint NOT NULL',
            'operator' => 'character varying(255) NOT NULL',
            'region_code_fz' => 'character(2) NOT NULL',
            'mnc' => 'character(2) NOT NULL',
            'route' => 'character(5) NOT NULL',
        ],
    ];

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */

    protected $downloadedFile = null;

    protected function readData()
    {
        $transaction = $this->_db->beginTransaction();

        try {
            $fileUrl = \Yii::getAlias('@runtime/' . $this->fileName);

            if (!is_file($fileUrl) || !is_readable($fileUrl)) {
                throw new NotFoundHttpException('Ошибка чтения файла ' . $fileUrl);
            }

            $xlsFileType = \PHPExcel_IOFactory::identify($fileUrl);
            $reader = \PHPExcel_IOFactory::createReader($xlsFileType);

            $excel = $reader->load($fileUrl);

            $sheet = $excel->getSheet(0);

            $insertValues = [];
            $this->startTrackingForDeletion();
            foreach ($sheet->getRowIterator(2) as $xlsRow) {
                $cellIterator = $xlsRow->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                $row = [];
                foreach ($cellIterator as $cell) {
                    if (!is_null($cell)) {
                        $row[] = $cell->getValue();
                    }
                }

                if (count($row) < 8 || !$row[0]) {
                    echo 'Неправильные данные: ' . print_r($row, true) . PHP_EOL;
                    continue;
                }

                /*
                 * (
                 *  [0] => 900
                 *  [1] => 0700000
                 *  [2] => 0999999
                 *  [3] => 300000
                 *  [4] => "Т2 Мобайл" ООО
                 *  [5] => 75
                 *  [6] => 20
                 *  [7] => D7520
                 * )
                 */

                $ndc = $row[0];
                $fullNumberFrom = (int)Country::RUSSIA_PREFIX . $ndc . $row[1];
                $fullNumberTo = (int)Country::RUSSIA_PREFIX . $ndc . $row[2];
                $operatorName = $row[4];
                $fzRegionCode = $row[5];
                $mnc = $row[6];
                $route = $row[7];

                $insertValues[] = [$ndc, $fullNumberFrom, $fullNumberTo, $operatorName, $fzRegionCode, $mnc, $route];

                if (count($insertValues) >= self::CHUNK_SIZE) {
                    $this->insertValues(Country::RUSSIA, $insertValues);
                }
            }

            if ($insertValues) {
                $this->insertValues(Country::RUSSIA, $insertValues);
            }
            $this->endTrackingForDeletion(Country::RUSSIA);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e);

            throw $e;
        }
    }

    /**
     * Скачать последний файл с портированными номерами
     *
     * @throws InvalidConfigException
     */
    public function actionDownloadFileData()
    {
        $this->downloadedFile =
            (new RouteMncDownloader())
                ->loadPage()
                ->parseFiles()
                ->findLast()
                ->download();

        return ExitCode::OK;
    }

    /**
     * Полный цикл портирования номеров (скачивание, обновление, линковка, синхронизация)
     */
    public function actionUpdate()
    {
        echo PHP_EOL . date('r') . ': Start porting numbers';

        if ($this->actionDownloadFileData() != ExitCode::OK) {
            throw new \LogicException('Error in actionDownloadFileData');
        }

        if (!$this->downloadedFile) {
            throw new \LogicException('Файл не скачен');
        }

        if (!preg_match('/^(.*).xls/', $this->downloadedFile, $matches) || !isset($matches[1])) {
            throw new \LogicException('Файл не распознан (' . $this->downloadedFile . ')');
        }

        echo PHP_EOL . 'Обработка файла: ' . $this->downloadedFile;

        echo PHP_EOL . date('r') . ': начало импорта';
        $this->fileName = $this->downloadedFile;
        if ($this->actionImport() != ExitCode::OK) {
            throw new \LogicException('Error in actionImport');
        }
//
//        echo PHP_EOL . date('r') . ': Создаем событие синхронизации';
//        $this->actionNotifyEventPortedNumber();
//
//
        echo PHP_EOL . date('r') . ': End porting numbers';
    }

}
