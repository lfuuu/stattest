<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\HistoryVersion;
use Yii;
use yii\base\Exception;
use yii\web\Response;

class VersionController extends BaseController
{
    /**
     * @return array
     */
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

    /**
     * @return string
     * @throws Exception
     */
    public function actionShow()
    {
        $getRequest = Yii::$app->request->get();
        if (!isset($getRequest['params']) || !is_array($getRequest['params'])) {
            throw new \InvalidArgumentException('params');
        }

        $params = $getRequest['params'];
        $models = [];
        $versionsQuery = HistoryVersion::find();
        foreach ($params as $param) {
            if (!is_array($param)) {
                throw new \InvalidArgumentException('param');
            }

            $modelName = $param[0];
            if (!class_exists($modelName)) {
                throw new \InvalidArgumentException('Bad model type');
            }

            if (!isset($models[$modelName])) {
                $models[$modelName] = new $modelName();
            }

            list($modelName, $modelId, $parenModelId) = $param;
            if ($modelId) {
                $versionsQuery->orWhere(['model' => $modelName, 'model_id' => $modelId]);
            } elseif ($parenModelId) {
                $versionsQuery->orWhere(['model' => $modelName, 'parent_model_id' => $parenModelId]);
            }
        }

        $versionsQuery->orderBy(['date' => SORT_ASC]);

        $versions = $versionsQuery->all();
        HistoryVersion::generateDifferencesFor($versions);
        // Переупаковка данных
        $odata = []; $ndata = [];
        foreach ($versions as $version) {
            /* @param HistoryVersion $version */
            foreach ($version->diffs as $key => $value) {
                if (count($value) !== 2) {
                    continue;
                }
                $odata[$key] = $value[0];
                $ndata[$key] = $value[1];
                $version->diffs[$key][0] = $odata[$key];
                $version->diffs[$key][1] = $ndata[$key];
            }
        }
        unset($odata, $ndata, $key, $value, $version);

        $this->layout = 'minimal';

        return Yii::$app->request->isAjax ?
            $this->renderPartial('show', ['versions' => $versions, 'models' => $models]) :
            $this->render('show', ['versions' => $versions, 'models' => $models]);
    }

    /**
     * @param string $model
     * @param int $modelId
     * @param string $date
     * @return array
     * @throws Exception
     */
    public function actionDelete($model, $modelId, $date)
    {
        $version = HistoryVersion::findOne(['model' => $model, 'model_id' => $modelId, 'date' => $date]);

        if (!$version) {
            throw new Exception('Version does not exists');
        }

        $version->delete();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }
}