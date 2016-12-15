<?php

namespace app\controllers\voip;

use Yii;
use app\models\voip\filter\Cdr;
use app\classes\BaseController;
use yii\filters\AccessControl;

class CdrController extends BaseController
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
                        'roles' => ['voip.access'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Контроллер страницы /voip/cdr
     *
     * @return string
     */
    public function actionIndex ()
    {
        $model = new Cdr();
        $model->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $model
        ]);
    }

}