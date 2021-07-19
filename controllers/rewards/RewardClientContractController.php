<?php

namespace app\controllers\rewards;

use app\classes\BaseController;
use app\forms\rewards\RewardClientContractFormNew;
use app\forms\rewards\RewardClientContractFormEdit;
use Yii;
use yii\filters\AccessControl;

class RewardClientContractController extends BaseController
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
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['clients.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var RewardClientContractFormNew $formModel */
        $formModel = new RewardClientContractFormNew();
        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['../contract/edit', 'id' => $formModel->id]);
        }

        return $this->render('edit', ['formModel' => $formModel]);
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
        /** @var RewardClientContractFormEdit $formModel */
        $formModel = new RewardClientContractFormEdit(['id' => $id]);
        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', $formModel->id ? 'The object was saved successfully' : 'The object was dropped successfully'));
            return $this->redirect(['../contract/edit', 'id' => $formModel->id]);
        }

        return $this->render('edit', ['formModel' => $formModel]);
    }

}
