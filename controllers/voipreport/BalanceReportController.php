<?php

namespace app\controllers\voipreport;

use app\classes\BaseController;
use app\forms\voipreport\BalanceReport;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;
use Yii;


class BalanceReportController extends BaseController
{

    public function actionIndex()
    {
        $filterModel = new BalanceReport();

        $isValidated = true;
        if (!$filterModel->load(Yii::$app->request->queryParams) || !$filterModel->validate()) {
            $isValidated = false;
        }
        $dataProvider = $filterModel->search($isValidated);

        return $this->render('index', [
            'filterModel' => $filterModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGetBusinessProcesses($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ($id) ? BusinessProcess::getList(true, false, $id) : ['' => '----'];
    }

    public function actionGetBusinessProcessStatuses($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ($id) ? BusinessProcessStatus::getList(true, false, $id) : ['' => '----'];
    }
}