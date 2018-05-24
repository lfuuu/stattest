<?php

namespace app\modules\callTracking\controllers;

use app\classes\BaseController;
use app\modules\callTracking\filter\LogFilter;
use Yii;
use yii\filters\AccessControl;

class LogController extends BaseController
{
    /**
     * Права доступа
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['nnp.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new LogFilter;
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}