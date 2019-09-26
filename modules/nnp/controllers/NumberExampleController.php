<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filters\NumberExampleFilter;
use app\modules\nnp\forms\numberExample\FormView;
use Yii;
use yii\filters\AccessControl;

/**
 * Примеры номеров
 */
class NumberExampleController extends BaseController
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
                        'actions' => ['index', 'view'],
                        'roles' => ['nnp.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        ini_set('memory_limit', '5G');

        $filterModel = new NumberExampleFilter();

        $get = Yii::$app->request->get();

        $filterModel->load($get);

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Просмотр
     *
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionView($id)
    {
        $formModel = new FormView([
            'id' => $id
        ]);

        return $this->render('view', [
            'formModel' => $formModel,
        ]);
    }
}
