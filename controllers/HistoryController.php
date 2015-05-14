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

    public function actionShow()
    {
        $models = [];
        $getRequest = Yii::$app->request->get();
        if(!$getRequest)
            throw new Exception('Models not exists');

        $changes = HistoryChanges::find()
            ->joinWith('user');
        foreach ($getRequest as $modelName => $modelId) {
            $className = 'app\\models\\' . $modelName;
            if (!class_exists($className)) {
                throw new Exception('Bad model type');
            }

            $changes->orWhere(['model' => $modelName, 'model_id' => $modelId]);
            $models[$modelName] = new $className();
        }

        $changes = $changes->orderBy('created_at desc')->all();

        $this->layout = 'minimal';

        return Yii::$app->request->isAjax
            ? $this->renderPartial('show', ['changes' => $changes, 'models' => $models])
            : $this->render('show', ['changes' => $changes, 'models' => $models]);
    }
}