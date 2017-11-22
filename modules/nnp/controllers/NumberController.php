<?php

namespace app\modules\nnp\controllers;

use app\classes\BaseController;
use app\modules\nnp\filters\NumberFilter;
use app\modules\nnp\models\Number;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Номера
 */
class NumberController extends BaseController
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
        $filterModel = new NumberFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $full_number
     * @return string
     * @throws \yii\db\Exception
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($full_number)
    {
        $number = Number::findOne(['full_number' => $full_number]);
        if (!$number) {
            throw new NotFoundHttpException();
        }

        if ($this->loadFromInput($number)) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'number' => $number,
        ]);
    }
}
