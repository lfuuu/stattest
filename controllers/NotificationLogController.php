<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\models\notifications\NotificationLog;

class NotificationLogController extends BaseController
{

    public function actionIndex()
    {
        $searchModel = new NotificationLog;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('report', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

}