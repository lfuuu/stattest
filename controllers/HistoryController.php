<?php
namespace app\controllers;

use app\models\ClientContragent;
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
                'roles' => ['clients.read'],
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

        foreach($changes as &$change){
            if(false !== strpos($change->data_json, 'contragent_id')){
                $data = json_decode($change->data_json, true);
                $dataPrev = json_decode($change->prev_data_json, true);
                $data['contragent_id'] = ClientContragent::findOne($data['contragent_id'])->name;
                $dataPrev['contragent_id'] = ClientContragent::findOne($dataPrev['contragent_id'])->name;
                $change->data_json = json_encode($data, JSON_FORCE_OBJECT);
                $change->prev_data_json = json_encode($dataPrev, JSON_FORCE_OBJECT);
            }
        }

        $this->layout = 'minimal';

        return Yii::$app->request->isAjax
            ? $this->renderPartial('show', ['changes' => $changes, 'models' => $models])
            : $this->render('show', ['changes' => $changes, 'models' => $models]);
    }
}