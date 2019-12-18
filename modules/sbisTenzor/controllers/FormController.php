<?php

namespace app\modules\sbisTenzor\controllers;

use app\classes\BaseController;
use app\modules\sbisTenzor\forms\form\IndexForm;
use yii\filters\AccessControl;

/**
 * FormController controller for the `sbisTenzor` module
 */
class FormController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['newaccounts_bills.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список форм первичных документов
     *
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $indexForm = new IndexForm();

        return $this->render('index', [
            'dataProvider' => $indexForm->getDataProvider(),
            'title' => $indexForm->getTitle(),
        ]);
    }
}
