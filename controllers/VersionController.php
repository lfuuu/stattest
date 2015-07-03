<?php
namespace app\controllers;

use app\models\HistoryVersion;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\web\Response;

class VersionController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['show'],
                'roles' => ['clients.read'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'roles' => ['clients.edit'],
            ],
        ];
        return $behaviors;
    }

    public function actionShow()
    {
        $getRequest = Yii::$app->request->get();
        if(!$getRequest)
            throw new Exception('Models not exists');

        $versions = HistoryVersion::find();
        $models = [];

        foreach($getRequest as $model => $id) {
            $className = 'app\\models\\' . $model;
            if (!class_exists($className)) {
                throw new Exception('Bad model type');
            }

            $versions->orWhere(['model' => $model, 'model_id' => $id]);
            $models[$model] = new $className();
        }

        $versions = $versions->all();

        HistoryVersion::generateDifferencesFor($versions);

        $this->layout = 'minimal';

        return Yii::$app->request->isAjax
            ? $this->renderPartial('show', ['versions' => $versions, 'models' => $models])
            : $this->render('show', ['versions' => $versions, 'models' => $models]);
    }

    public function actionDelete($model, $modelId, $date)
    {
        $version = HistoryVersion::findOne(['model' => $model, 'model_id' => $modelId, 'date' => $date]);

        if(!$version)
            throw new Exception('Version not exists');

        $version->delete();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }
}