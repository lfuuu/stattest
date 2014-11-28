<?php
namespace app\controllers\tariffication;

use app\models\tariffication\Service;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;

class ServiceController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'list' => Service::find()->all(),
        ]);
    }
}
