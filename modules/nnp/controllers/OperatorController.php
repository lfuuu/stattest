<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filters\OperatorFilter;
use app\modules\nnp\forms\operator\FormEdit;
use app\modules\nnp\forms\operator\FormNew;
use Yii;
use yii\filters\AccessControl;

/**
 * Операторы
 */
class OperatorController extends BaseController
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
        $filterModel = new OperatorFilter();
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
        }

        if ($formModel->isSaved) {
            return $this->redirect(['index', 'OperatorFilter[country_code]' => $formModel->operator->country_code]);
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
            return $this->redirect($formModel->getOperatorModel()->getUrl());
        }

        if ($formModel->isSaved) {
            \Yii::$app->session->addFlash('success', 'Данные сохранены');
            return $this->redirect($formModel->getOperatorModel()->getUrl());
        }

        return $this->render('edit', [
            'formModel' => $formModel,
        ]);
    }
}
