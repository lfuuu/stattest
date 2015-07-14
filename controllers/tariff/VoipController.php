<?php

namespace app\controllers\tariff;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use app\models\TariffVoip;
use app\forms\tariff\voip\TariffVoipListForm;
use app\forms\tariff\voip\TariffVoipForm;

class VoipController extends BaseController
{

    /*
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['organization.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'duplicate'],
                        'roles' => ['organization.edit'],
                    ],
                ],
            ],
        ];
    }
    */

    public function actionIndex()
    {
        $model = new TariffVoipListForm;
        $model->load(Yii::$app->request->getQueryParams());

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $model,
        ]);
    }

    public function actionAdd()
    {
        $model = new TariffVoipForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->scenario == 'save' && $model->save()) {
                $this->redirect('index');
            }
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

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->scenario == 'save' && $model->save()) {
                $this->redirect('index');
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }

}