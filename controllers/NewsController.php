<?php
namespace app\controllers;

use app\models\News;
use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;
use yii\web\Response;

class NewsController extends BaseController
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
        $news = News::find()
            ->orWhere(['user_id' => Yii::$app->user->id])
            ->orWhere(['to_user_id' => 0])
            ->orWhere(['to_user_id' => Yii::$app->user->id])
            ->orderBy(['id' => SORT_DESC])
            ->limit(30)
            ->all();
        return $this->render('index', ['news' => $news]);
    }

    public function actionCreate()
    {
        $model = new News;
        $data = Yii::$app->request->post();
        $model->message = $data['message'];
        $model->user_id = Yii::$app->user->id;
        $model->to_user_id = $data['to_user_id'];
        $model->priority = $data['priority'];
        $model->date = date('Y-m-d H:i:s');

        if($model->save())
            $res = ['status' => 'ok'];
        else
            $res = ['status' => 'error'];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    public function actionLast($lastId)
    {
        $news = News::find()->andWhere(['>', 'id', $lastId])
            ->orWhere(['user_id' => Yii::$app->user->id])
            ->orWhere(['to_user_id' => 0])
            ->orWhere(['to_user_id' => Yii::$app->user->id])
            ->limit(50)->all();
        $this->layout = false;
        krsort($news);
        foreach($news as $item)
            echo $this->render('_message', ['item' => $item]);
        die;
    }
}

