<?php
namespace app\controllers;

use app\models\SaleChannelOld;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\data\ActiveDataProvider;


class SaleChannelController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'roles' => ['clients.edit'],
            ],
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SaleChannelOld::find(),
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $model = new SaleChannelOld();
        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['sale-channel/index']);
        }

        return $this->render('edit', ['model' => $model]);
    }

    public function actionEdit($id)
    {
        $model = SaleChannelOld::findOne($id);
        if(!$model)
            throw new Exception('Sale Channel does not exist');

        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['sale-channel/index']);
        }

        return $this->render('edit', ['model' => $model]);
    }
}
