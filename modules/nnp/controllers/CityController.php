<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filters\CityFilter;
use app\modules\nnp\forms\city\FormEdit;
use app\modules\nnp\forms\city\FormNew;
use Yii;
use yii\filters\AccessControl;

/**
 * Города
 */
class CityController extends BaseController
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
        $filterModel = new CityFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var FormNew $formModel */
        $formModel = new FormNew();

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
            return $this->redirect($formModel->city->getUrl());
        }

        if ($formModel->isSaved) {
            \Yii::$app->session->addFlash('success', 'Данные сохранены');
            return $this->redirect($formModel->city->getUrl());
        }

        return $this->render('edit', [
            'formModel' => $formModel,
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
            return $this->redirect($formModel->city->getUrl());
        }

        if ($formModel->isSaved) {
            return $this->redirect($formModel->city->getUrl());
//            return $this->redirect(['index', 'CityFilter[country_code]' => $formModel->city->country_code, 'CityFilter[region_id]' => $formModel->city->region_id]);
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }
}
