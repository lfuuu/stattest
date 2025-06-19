<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\modules\sim\filters\CardStatusFilter;
use app\modules\sim\models\CardStatus;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Статусы SIM-карт
 */
class CardStatusController extends BaseController
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
                        'roles' => ['sim.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['sim.read'],
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
        $filterModel = new CardStatusFilter();
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
        $cardStatus = new CardStatus;
        if ($this->loadFromInput($cardStatus)) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'cardStatus' => $cardStatus,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        $cardStatus = CardStatus::findOne(['id' => $id]);
        if (!$cardStatus) {
            throw new NotFoundHttpException();
        }

        if ($this->loadFromInput($cardStatus)) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'cardStatus' => $cardStatus,
        ]);
    }
}
