<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use yii\filters\AccessControl;

/**
 * Default
 */
class DefaultController extends BaseController
{
    /**
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
        return $this->redirect('/nnp/number-range/');
    }

}
