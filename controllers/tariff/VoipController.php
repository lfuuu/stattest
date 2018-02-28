<?php

namespace app\controllers\tariff;

use app\classes\Assert;
use app\classes\BaseController;
use app\forms\tariff\voip\TariffVoipForm;
use app\forms\tariff\voip\TariffVoipListForm;
use app\models\TariffVoip;
use app\models\User;
use Yii;
use yii\filters\AccessControl;

class VoipController extends BaseController
{

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
                        'actions' => ['add', 'edit'],
                        'roles' => ['tarifs.edit'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new TariffVoipListForm;
        $model->load(Yii::$app->request->queryParams);

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $model,
        ]);
    }

    public function actionAdd($fromTariffId = null)
    {
        $model = new TariffVoipForm;

        Yii::$app->request->isPost && $fromTariffId && $fromTariffId = null;

        if ($fromTariffId) {
            $tariff = TariffVoip::findOne($fromTariffId);
            Assert::isObject($tariff);

            $model->setAttributes($tariff->getAttributes(null, ['id']), false);
        } else {
            $data = Yii::$app->request->post();
        }

        if (!$fromTariffId && $model->load($data) && $model->validate() && $model->save()) {
            $this->redirect('index');
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => true,
        ]);
    }

    public function actionEdit($id)
    {
        $model = new TariffVoipForm;

        $tariff = TariffVoip::findOne($id);
        Assert::isObject($tariff);

        $model->setAttributes($tariff->getAttributes(), false);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($tariff)) {
            $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render('edit', [
            'model' => $model,
            'user' => User::findOne($model->edit_user),
            'creatingMode' => false,
        ]);
    }

}