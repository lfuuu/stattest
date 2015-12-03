<?php

namespace app\controllers\important_events;

use Yii;
use app\classes\BaseController;
use app\models\important_events\ImportantEvents;

class ReportController extends BaseController
{

    public function actionIndex()
    {
        $searchModel = new ImportantEvents;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

}