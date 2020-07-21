<?php

namespace app\modules\nnp2\controllers;

use app\classes\BaseController;
use app\modules\nnp2\filters\RangeShortFilter;
use app\modules\nnp2\forms\rangeShort\FormView;
use Yii;
use yii\filters\AccessControl;

/**
 * Диапазон номеров (готовый)
 */
class RangeShortController extends BaseController
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
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new RangeShortFilter();
        if ($sort = \Yii::$app->request->get('sort')) {
            $filterModel->sort = $sort;
        }

        $get = Yii::$app->request->get();
        if (!isset($get['RangeShortFilter'])) {
            $get['RangeShortFilter']['is_active'] = 1; // по умолчанию только "вкл."
        }

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
