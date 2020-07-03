<?php

namespace app\modules\nnp2\controllers;

use app\classes\BaseController;
use app\modules\nnp2\filters\NumberRangeFilter;
use app\modules\nnp2\forms\numberRange\FormEdit;
use Yii;
use yii\filters\AccessControl;

/**
 * Диапазон номеров
 */
class NumberRangeController extends BaseController
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
                        'roles' => ['nnp.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['edit'],
                        'roles' => ['nnp.write'],
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
        $filterModel = new NumberRangeFilter();

        $get = Yii::$app->request->get();
        if (!isset($get['NumberRangeFilter'])) {
            $get['NumberRangeFilter']['is_active'] = 1; // по умолчанию только "вкл."
        }

        $filterModel->load($get);

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        /** @var FormEdit $formModel */
        $formModel = new FormEdit([
            'id' => $id
        ]);

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            return $this->redirect(['index', 'NumberRangeFilter[country_code]' => $formModel->numberRange->geoPlace->country_code, 'NumberRangeFilter[is_active]' => 1]);
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }
}
