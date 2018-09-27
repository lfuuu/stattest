<?php

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\classes\dictionary\forms\BusinessProcessStatusFormEdit;
use app\classes\dictionary\forms\BusinessProcessStatusFormNew;
use app\models\filter\BusinessProcessStatusFilter;
use Yii;
use yii\filters\AccessControl;

class BusinessProcessStatusController extends BaseController
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
                        'roles' => ['dictionary-statuses.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['dictionary-statuses.write'],
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
        $filterModel = new BusinessProcessStatusFilter();
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
        /** @var BusinessProcessStatusFormNew $form */
        $form = new BusinessProcessStatusFormNew();

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
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
        /** @var BusinessProcessStatusFormEdit $form */
        $form = new BusinessProcessStatusFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
            return $this->redirect(['index', 'BusinessProcessStatusFilter' => ['business_process_id' => $form->getStatusModel()->business_process_id]]);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }
}