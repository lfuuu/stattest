<?php
namespace app\controllers;

use Yii;
use DateTime;
use app\classes\BaseController;
use app\classes\operators\OperatorsFactory;
use app\classes\operators\OperatorOnlime;

class ReportsController extends BaseController
{

    public function actionOnlimeReport()
    {
        $filter = Yii::$app->request->get('filter', []);

        if (isset($filter['range']))
            list($dateFrom, $dateTo) = explode(' : ', $filter['range']);
        else {
            $today = new DateTime('now');
            $firstDayThisMonth = clone $today;
            $lastDayThisMonth = clone $today;

            $dateFrom = $firstDayThisMonth->modify('first day of this month')->format('Y-m-d');
            $dateTo = $lastDayThisMonth->modify('last day of this month')->format('Y-m-d');
        }

        $operator = OperatorsFactory::me()->getOperator(OperatorOnlime::OPERATOR_CLIENT);
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

    public function actionOnlime2Report()
    {
        $operator = OperatorsFactory::me()->getOperator(OperatorOnlime::OPERATOR_CLIENT);

    }

    public function actionOnlimeAllReport()
    {
        $operator = OperatorsFactory::me()->getOperator(OperatorOnlime::OPERATOR_CLIENT);

    }

    public function actionOnlimeDevicesReport()
    {
        $filter = Yii::$app->request->get('filter', []);

        $dateFrom = $dateTo = '';
        if (isset($filter['range']))
            list($dateFrom, $dateTo) = explode(' : ', $filter['range']);

        $operator = OperatorsFactory::me()->getOperator('onlime-devices');
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