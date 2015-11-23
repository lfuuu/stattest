<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\models\ImportantEvents;

class ImportantEventsController extends BaseController
{

    public function actionIndex()
    {
        $searchModel = new ImportantEvents;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('report', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

}