<?php

namespace app\controllers\tariff;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\TariffVoipPackage;
use app\forms\tariff\voip_package\TariffVoipPackageForm;
use app\forms\tariff\voip_package\TariffVoipPackageListForm;

class VoipPackageController extends BaseController
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
        $model = new TariffVoipPackageListForm;
        $model->load(Yii::$app->request->queryParams);

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $model,
        ]);
    }

    public function actionAdd()
    {
        $model = new TariffVoipPackageForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('index');
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => true,
        ]);
    }

    public function actionEdit($id)
    {
        $model = new TariffVoipPackageForm;

        $tariff = TariffVoipPackage::findOne($id);
        Assert::isObject($tariff);

        $model->setAttributes($tariff->getAttributes(), false);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($tariff)) {
            $this->redirect(['edit', 'id' => $model->id]);
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }

}