<?php

namespace app\controllers\voipreport;

use app\classes\Assert;
use app\classes\BaseController;
use app\classes\traits\PgsqlArrayFieldParseTrait;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\GeoCountry;
use app\models\billing\GeoRegion;
use app\models\billing\PricelistReport;
use app\models\Currency;
use app\models\CurrencyRate;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\Response;

class PricelistReportController extends BaseController
{

    use PgsqlArrayFieldParseTrait;

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        $searchModel = new PricelistReport;
        $searchModel->report_type_id = PricelistReport::REGION_TYPE_ANALYZE;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

    /**
     * @param int $reportId
     * @return string
     * @throws InvalidParamException
     */
    public function actionCalculate($reportId)
    {
        /** @var PricelistReport $pricelistReport */
        $pricelistReport = PricelistReport::findOne($reportId);
        Assert::isObject($pricelistReport);
        $pricelistReport->prepareData();

        return $this->render('calculate', [
            'pricelistReportId' => $pricelistReport->id,
            'pricelistReportData' => $pricelistReport->getData(),
            'currencyMap' => Currency::map(),
            'countries' => GeoCountry::getList($isWithEmpty = true),
            'regions' => GeoRegion::getList($isWithEmpty = true),
        ]);
    }

    /**
     * @param int $reportId
     * @param string $currency
     * @return array
     * @throws InvalidParamException
     */
    public function actionGetPricelistData($reportId, $currency = Currency::RUB)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var PricelistReport $pricelistReport */
        $pricelistReport = PricelistReport::findOne($reportId);
        Assert::isObject($pricelistReport);

        $pricelistReport->prepareData();
        $pricelistReportData = $pricelistReport->getData();

        $result = [];

        foreach (PricelistReport::getPricelistData($reportId) as $dataRow) {
            $row = [
                'prefix' => $dataRow['prefix'],
                'destination' => $dataRow['destination'],
                'country' => $dataRow['country'],
                'region' => $dataRow['region'],
                'zone' => $dataRow['zone'],
                'mob' => $dataRow['mob'],
            ];

            $orders = $this->_parseFieldValue($dataRow['orders']) ?: [];
            $prices = $this->_parseFieldValue($dataRow['prices']) ?: [];
            $pricelistIds = $this->_parseFieldValue($dataRow['pricelist_ids']) ?: [];

            foreach ($orders as $index => $position) {
                $pricelistId = array_key_exists($position, $pricelistIds) ? $pricelistIds[$position] : 0;
                $price = array_key_exists($position, $prices) ? $prices[$position] : 0;

                if (
                    $pricelistId
                    && array_key_exists($pricelistId, $pricelistReportData)
                ) {
                    $pricelistCurrency = $pricelistReportData[$pricelistId]['pricelist']->currency_id;
                    $pricelistCurrencyRate = CurrencyRate::dao()->crossRate($pricelistCurrency, $currency);

                    if (!is_null($pricelistCurrencyRate)) {
                        $price = round($pricelistCurrencyRate * $price, 4);
                    }
                }

                $prices[$position] = $price;
            }

            if (count($orders) > 0) {
                $row['best_price_1'] = $prices[$orders[0]];
                $row['best_price_2'] = $prices[$orders[1]];
            } else {
                $row['best_price_1'] = $row['best_price_2'] = 0;
            }

            foreach ($prices as $index => $price) {
                $row['price_' . $index] = $price;
            }

            $result[] = $row;
        }

        return ['data' => $result];
    }

    /**
     * @param int $reportId
     * @throws InvalidParamException
     * @throws HttpException
     */
    public function actionGetPricelistExport($reportId)
    {
        /** @var PricelistReport $pricelistReport */
        $pricelistReport = PricelistReport::findOne($reportId);
        $exportData = Json::decode(Yii::$app->request->post('data'));

        $firstRow = [
            'Префикс номера', 'Зона', 'Назначение', 'Лучшая цена #1', 'Лучшая цена #2', 'Результат',
        ];
        $secondRow = [
            '', '', '', '', '', '',
        ];

        foreach ($pricelistReport::getPricelists($pricelistReport->getPricelistsIds()) as $pricelist) {
            $firstRow[] = $pricelist->name;
        }

        foreach ($pricelistReport->getDates() as $date) {
            $secondRow[] = $date;
        }

        $csv = fopen('php://temp/maxmemory:'. (5 * 1024 * 1024), 'r+');
        //add BOM to fix UTF-8 in Excel
        fwrite($csv, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        fputcsv($csv, $firstRow, ';');
        fputcsv($csv, $secondRow, ';');

        foreach ($exportData as $row) {
            $csvRow = [
                $row['prefix'],
                $row['zone'],
                $row['destination'],
                $row['best_price_1'],
                $row['best_price_2'],
                array_key_exists('modify_result', $row) ? $row['modify_result'] : 0
            ];

            foreach ($pricelistReport->getDates() as $index => $date) {
                $csvRow[] = $row['price_' . $index];
            }

            fputcsv($csv, $csvRow, ';');
        }

        rewind($csv);

        Yii::$app->response->sendContentAsFile(
            stream_get_contents($csv),
            'analize-pricelist-' . date(DateTimeZoneHelper::DATETIME_FORMAT) . '.csv'
        );
    }

}