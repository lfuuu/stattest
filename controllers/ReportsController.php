<?php
namespace app\controllers;

use Yii;
use DateTime;
use app\classes\BaseController;
use app\classes\operators\OperatorsFactory;
use app\classes\operators\OperatorOnlime;
use app\classes\operators\OperatorOnlimeDevices;
use app\classes\operators\OperatorOnlimeStb;

class ReportsController extends BaseController
{

    public function actionOnlimeReport()
    {
        $filter = Yii::$app->request->get('filter', []);
        $asFile = Yii::$app->request->get('as-file', 0);

        if (isset($filter['range'])) {
            list($dateFrom, $dateTo) = explode(' : ', $filter['range']);
        }
        else {
            $today = new DateTime('now');
            $firstDayThisMonth = clone $today;
            $lastDayThisMonth = clone $today;

            $dateFrom = $firstDayThisMonth->modify('first day of this month')->format('Y-m-d');
            $dateTo = $lastDayThisMonth->modify('last day of this month')->format('Y-m-d');
        }

        $operator = OperatorsFactory::me()->getOperator(OperatorOnlime::OPERATOR_CLIENT);
        $report = $operator->getReport()->getReportResult($dateFrom, $dateTo, $filter['mode'], $filter['promo']);

        if ($asFile == 1) {
            $reportName = 'OnLime__' . $filter['mode'] . '__' . $dateFrom . '__' . $dateTo;

            Yii::$app->response->sendContentAsFile(
                $operator->GenerateExcel($report),
                $reportName . '.xls'
            );
            Yii::$app->end();
        }

        return $this->render('onlime/report.php', [
            'operator' => $operator,
            'report' => $report,
            'filter' => [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'range' => isset($filter['range']) ? $filter['range'] : '',
                'mode' => isset($filter['mode']) ? $filter['mode'] : '',
                'promo' => isset($filter['promo']) ? $filter['promo'] : '',
            ],
        ]);
    }

    public function actionOnlimeDevicesReport()
    {
        $filter = Yii::$app->request->get('filter', []);

        if (isset($filter['range'])) {
            list($dateFrom, $dateTo) = explode(' : ', $filter['range']);
        }
        else {
            $today = new DateTime('now');
            $firstDayThisMonth = clone $today;
            $lastDayThisMonth = clone $today;

            $dateFrom = $firstDayThisMonth->modify('first day of this month')->format('Y-m-d');
            $dateTo = $lastDayThisMonth->modify('last day of this month')->format('Y-m-d');
        }

        $operator = OperatorsFactory::me()->getOperator(OperatorOnlimeDevices::OPERATOR_CLIENT);
        $report = $operator->getReport()->getReportResult($dateFrom, $dateTo, $filter['mode'], $filter['promo']);

        return $this->render('onlime/report.php', [
            'operator' => $operator,
            'report' => $report,
            'filter' => [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'range' => isset($filter['range']) ? $filter['range'] : '',
                'mode' => isset($filter['mode']) ? $filter['mode'] : '',
                'promo' => isset($filter['promo']) ? $filter['promo'] : '',
            ],
        ]);
    }

}