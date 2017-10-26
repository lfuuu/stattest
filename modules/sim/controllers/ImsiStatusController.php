<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\modules\sim\filters\ImsiStatusFilter;
use app\modules\sim\models\ImsiStatus;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Статусы IMSI
 */
class ImsiStatusController extends BaseController
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
                        'actions' => ['index'],
                        'roles' => ['sim.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['sim.write'],
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
        $filterModel = new ImsiStatusFilter();
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
        $imsiStatus = new ImsiStatus;
        if ($this->loadFromInput($imsiStatus)) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'imsiStatus' => $imsiStatus,
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
        $imsiStatus = ImsiStatus::findOne(['id' => $id]);
        if (!$imsiStatus) {
            throw new NotFoundHttpException();
        }

        if ($this->loadFromInput($imsiStatus)) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'imsiStatus' => $imsiStatus,
        ]);
    }
}
