<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\models\HistoryVersion;

class VersionController extends BaseController
{

    const DATE_THIS = 0,
        DATE_FROM = 1,
        DATE_TO = 2,
        DATE_FROM_TO = 3;

    public function actionIndex($modelName = null, $modelId = null, $date = null, $dateType = self::DATE_THIS)
    {

        $model = HistoryVersion::find();
        if (!empty($modelName)) {
            $model->andWhere(['model' => $modelName]);
            if (!empty($modelId))
                $model->andWhere(['model_id' => $modelId]);
        }

        if (!empty($date)) {
            switch ($dateType) {
                case static::DATE_FROM:
                    $model->andWhere('date >= :date', ['date' => $date]);
                    break;
                case static::DATE_TO:
                    $model->andWhere('date <= :date', ['date' => $date]);
                    break;
                case static::DATE_FROM_TO:
                    $dateArr = explode('&', $date);
                    $model->andWhere('date BETWEEN :date1 AND :date2', ['date1' => $dateArr[0], 'date2' => $dateArr[1]]);
                    break;
                case static::DATE_THIS:
                    $model->andWhere(['date' => $date]);
                    break;
            }
        }

        if (empty($date) || empty($modelId))
            $model->limit(100);


        $versions = HistoryVersion::generateVersionsJson($model->asArray()->all());
        return (Yii::$app->request->isAjax) ? $versions : $this->renderPartial('index', ['versions' => $versions]);
    }

    public function actionList($modelName, $modelId)
    {
        $this->layout = 'minimal';


        $model = HistoryVersion::find()
            ->andWhere(['model' => $modelName])
            ->andWhere(['model_id' => $modelId]);

        $versions = $model->asArray()->all();
        HistoryVersion::generateDifferencesFor($versions);

        return $this->render('list', ['versions' => $versions]);
    }

    public function actionSetdate($modelName, $modelId, $date, $dateTo)
    {
        $model = HistoryVersion::find()
            ->andWhere(['model' => $modelName])
            ->andWhere(['model_id' => $modelId])
            ->andWhere(['date' => $date])
            ->one();

        $modelNew = HistoryVersion::findOne([
            'model' => $modelName,
            'model_id' => $modelId,
            'date' => $dateTo,
        ]);

        if ($modelNew !== null) {
            $modelNew->data_json = $model->data_json;
            $modelNew->save();
            $model->delete();
        } else {
            $model->date = $dateTo;
            $model->save();
        }

        $this->redirect(['version/list', 'modelName' => $modelName, 'modelId' => $modelId]);
    }

    public function delete($modelName, $modelId, $date, $key, $value)
    {
        $model = HistoryVersion::find()
            ->andWhere(['model' => $modelName])
            ->andWhere(['model_id' => $modelId])
            ->andWhere(['date' => $date])
            ->one();
        $newJSON = json_decode($model->data_json, true);
        $newJSON[$key] = $value;
        $model->data_json = json_encode($newJSON, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $model->save();

        $this->redirect(['version/list', 'modelName' => $modelName, 'modelId' => $modelId]);
    }
}
