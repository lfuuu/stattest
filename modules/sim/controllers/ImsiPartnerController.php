<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\modules\sim\filters\ImsiPartnerFilter;
use app\modules\sim\models\ImsiPartner;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * MVNO-партнеры IMSI
 */
class ImsiPartnerController extends BaseController
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
//                    [
//                        'allow' => true,
//                        'actions' => ['index'],
//                        'roles' => ['sim.read'],
//                    ],
//                    [
//                        'allow' => true,
//                        'actions' => ['new', 'edit'],
//                        'roles' => ['sim.write'],
//                    ],
                    [
                        'allow' => false,
                    ]
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
        $filterModel = new ImsiPartnerFilter();
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
     * @throws \yii\db\Exception
     */
    public function actionNew()
    {
        $imsiPartner = new ImsiPartner;
        if ($this->loadFromInput($imsiPartner)) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'imsiPartner' => $imsiPartner,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function actionEdit($id)
    {
        $imsiPartner = ImsiPartner::findOne(['id' => $id]);
        if (!$imsiPartner) {
            throw new NotFoundHttpException();
        }

        if ($this->loadFromInput($imsiPartner)) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'imsiPartner' => $imsiPartner,
        ]);
    }
}
