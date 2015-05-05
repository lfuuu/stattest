<?php
namespace app\controllers;

use app\models\HistoryChanges;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;

class HistoryController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['show'],
                'roles' => ['@'],
            ],
        ];
        return $behaviors;
    }

    public function actionShow($model, $model_id)
    {
        $className = 'app\\models\\' . $model;
        if (!class_exists($className)) {
            throw new Exception('Bad model type');
        }

        $this->layout = 'minimal';

        $changes =
            HistoryChanges::find()
                ->joinWith('user')
                ->andWhere(['model' => $model])
                ->andWhere(['model_id' => $model_id])
                ->orderBy('created_at desc')
                ->all();



        return Yii::$app->request->isAjax ?
                $this->renderPartial('show', [
                    'model' => new $className(),
                    'changes' => $changes,
                ]) : $this->render('show', [
                    'model' => new $className(),
                    'changes' => $changes,
        ]);
    }
}