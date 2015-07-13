<?php
namespace app\controllers\tariff;

use app\forms\tariff\TariffNumberAddForm;
use app\forms\tariff\TariffNumberEditForm;
use app\forms\tariff\TariffNumberListForm;
use app\models\TariffNumber;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;
use yii\web\BadRequestHttpException;

class NumberController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new TariffNumberListForm();
        $model->load(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $model->spawnDataProvider(),
            'filterModel' => $model,
        ]);
    }

    public function actionAdd()
    {
        $model = new TariffNumberAddForm();
        $model->activation_fee = 0;
        $model->periodical_fee = 0;

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
        $model = new TariffNumberEditForm();

        $tariff = TariffNumber::findOne($id);
        if ($tariff === null) throw new BadRequestHttpException();
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
