<?php

namespace app\controllers\report\accounting;

use app\classes\BaseController;
use app\helpers\DateTimeZoneHelper;
use app\models\filter\OperatorPayFilter;
use app\models\filter\PayReportFilter;
use Yii;

class PayReportController extends BaseController
{
    /**
     * Вывод списка
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $this->view->title = 'Отчет по платежам';
        $filterModel = new PayReportFilter([
            'add_date_from' => (new \DateTime('now'))->modify('-2 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            'add_date_to' => (new \DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT)
        ]);

        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}