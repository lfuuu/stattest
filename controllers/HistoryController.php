<?php
namespace app\controllers;

use app\classes\BaseController;
use app\models\ClientContragent;
use Yii;
use yii\db\ActiveQuery;

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
                'actions' => ['show'],
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

            // формат параметров см. в \app\classes\model\HistoryActiveRecord::getHistoryIds и script.js
            switch (count($param)) {

                case 2:
                    // Существующая модель. Класс и ID
                    list($modelName, $modelId) = $param;
                    $changesQuery->orWhere(['model' => $modelName, 'model_id' => $modelId]); // insert, update существующих
                    break;

                case 3:
                    // Удаленная модель. Класс, поле и значение
                    list($modelName, $fieldName, $fieldValue) = $param;
                    $changesQuery->orWhere([
                        'AND',
                        ['model' => $modelName],
                        [
                            'OR', // LIKE по json-строке - это извращение, но ничего лучше не придумал
                            ['LIKE', 'data_json', sprintf('"%s":%d,', $fieldName, $fieldValue)], // insert удаленных
                            ['LIKE', 'prev_data_json', sprintf('"%s":%d,', $fieldName, $fieldValue)], // delete
                            ['LIKE', 'data_json', sprintf('"%s":"%s",', $fieldName, $fieldValue)], // insert удаленных, строковые значения
                            ['LIKE', 'prev_data_json', sprintf('"%s":"%s",', $fieldName, $fieldValue)], // delete, строковые значения
                        ]
                    ]);
                    break;

                default:
                    throw new \InvalidArgumentException('param');
            }
        }

        /** @var ActiveQuery $changesQuery */
        $changesQuery = $changesQuery->orderBy('created_at desc');
        if (Yii::$app->request->isAjax && isset($getOptions['showLastChanges'])) {
            $changesQuery = $changesQuery->limit(isset($getOptions['howMany']) ? (int)$getOptions['howMany'] : 1);
        }

        /** @var \app\models\HistoryChanges[] $changes */
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

}