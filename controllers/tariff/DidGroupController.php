<?php

namespace app\controllers\tariff;

use app\classes\BaseController;
use app\forms\tariff\DidGroupFormEdit;
use app\forms\tariff\DidGroupFormNew;
use app\models\DidGroup;
use app\models\filter\DidGroupFilter;
use Yii;
use yii\filters\AccessControl;

class DidGroupController extends BaseController
{
    /**
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
                        'roles' => ['tarifs.read'],
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
        $filterModel = new DidGroupFilter();
        $filterModel->load(Yii::$app->request->getQueryParams());

        return $this->render('index', ['filterModel' => $filterModel]);
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var DidGroupFormNew $formModel */
        $formModel = new DidGroupFormNew();

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['index']);
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
        /** @var DidGroupFormEdit $formModel */
        $formModel = new DidGroupFormEdit(['id' => $id]);

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', $formModel->id ? 'The object was saved successfully' : 'The object was dropped successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('edit', ['formModel' => $formModel]);
    }

    /**
     * Назаначение DID-групп к номерам
     *
     * @throws \Exception
     */
    public function actionApply()
    {
        DidGroup::dao()->applyDidGroupToNumbers();

        Yii::$app->session->addFlash('success', \Yii::t('number', 'The DID-group scheme is applied to the numbers'));
        return $this->redirect('/tariff/did-group/');
    }

}
