<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\ClientContragent;
use app\models\filter\HistoryChangesFilter;
use Yii;

class HistoryController extends BaseController
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
                'actions' => ['show', 'index'],
                'roles' => ['clients.read'],
            ],
        ];
        return $behaviors;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function actionShow()
    {
        $getRequest = Yii::$app->request->get();
        if (!isset($getRequest['params']) || !is_array($getRequest['params'])) {
            throw new \InvalidArgumentException('params');
        }

        $params = $getRequest['params'];
        $models = [];
        $changesQuery = \app\models\HistoryChanges::find();
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
                $changesQuery->orWhere(['model' => $modelName, 'model_id' => $modelId]);
            } elseif ($parenModelId) {
                $changesQuery->orWhere(['model' => $modelName, 'parent_model_id' => $parenModelId]);
            }
        }

        /** @var \app\models\HistoryChanges[] $changes */
        $changesQuery = $changesQuery->orderBy([
            'model' => SORT_ASC, // групировать по model + model_id
            'model_id' => SORT_DESC,
            'id' => SORT_DESC, // по убыванию
        ]);
        $changes = $changesQuery->all();

        foreach ($changes as &$change) {
            if (false !== strpos($change->data_json, 'contragent_id')) {
                $data = json_decode($change->data_json, true);
                $dataPrev = json_decode($change->prev_data_json, true);
                $data['contragent_id'] = ClientContragent::findOne($data['contragent_id'])->name;
                $dataPrev['contragent_id'] = ClientContragent::findOne($dataPrev['contragent_id'])->name;
                $change->data_json = json_encode($data, JSON_FORCE_OBJECT);
                $change->prev_data_json = json_encode($dataPrev, JSON_FORCE_OBJECT);
            }
        }

        unset($change);
        $this->layout = 'minimal';

        return Yii::$app->request->isAjax ?
            $this->renderPartial('show', ['changes' => $changes, 'models' => $models]) :
            $this->render('show', ['changes' => $changes, 'models' => $models]);
    }

    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new HistoryChangesFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }
}