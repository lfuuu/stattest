<?php

namespace app\modules\nnp2\controllers;

use app\classes\BaseController;
use app\modules\nnp2\filters\RegionFilter;
use app\modules\nnp2\forms\region\FormEdit;
use Yii;
use yii\filters\AccessControl;

/**
 * Регионы
 */
class RegionController extends BaseController
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
                        'actions' => ['new', 'edit'],
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
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new RegionFilter();
        if ($sort = \Yii::$app->request->get('sort')) {
            $filterModel->sort = $sort;
        }
        $filterModel->load(Yii::$app->request->get());

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
            return $this->redirect(['index', 'RegionFilter[country_code]' => $formModel->region->country_code]);
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }
}
