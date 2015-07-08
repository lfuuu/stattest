<?php
namespace app\controllers;

use app\classes\grid\FilterDataProvider;
use app\models\SaleChannel;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;


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
        $dataProvider = new FilterDataProvider([
            'query' => SaleChannel::find(),
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $model = new SaleChannel();
        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['sale-channel/index']);
        }

        return $this->render('edit', ['model' => $model]);
    }

    public function actionEdit($id)
    {
        $model = SaleChannel::findOne($id);
        if(!$model)
            throw new Exception('Sale Channel does not exist');

        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['sale-channel/index']);
        }

        return $this->render('edit', ['model' => $model]);
    }
}