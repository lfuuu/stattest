<?php
/**
 * Типы услуг
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\modules\uu\filter\ServiceTypeFilter;
use app\modules\uu\forms\ServiceTypeEditForm;
use Yii;
use yii\filters\AccessControl;


class ServiceTypeController extends BaseController
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
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['tarifs.edit'],
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
        $filterModel = new ServiceTypeFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render(
            'index',
            [
                'filterModel' => $filterModel,
            ]
        );
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
        try {
            /** @var ServiceTypeEditForm $formModel */
            $formModel = new ServiceTypeEditForm(
                [
                    'id' => $id
                ]
            );
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render(
                '//layouts/empty',
                [
                    'content' => '',
                ]
            );
        }

        if ($formModel->isSaved) {

            if ($formModel->id) {

                Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
                return $this->redirect(
                    [
                        'edit',
                        'id' => $formModel->id,
                    ]
                );

            } else {

                Yii::$app->session->setFlash('success', Yii::t('common', 'The object was dropped successfully'));
                return $this->redirect(
                    [
                        'index',
                    ]
                );

            }
        }

        return $this->render(
            'edit',
            [
                'formModel' => $formModel,
            ]
        );

    }
}