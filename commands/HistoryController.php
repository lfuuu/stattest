<?php

namespace app\commands;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\ClientContact;
use app\models\HistoryChanges;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipNdcType;
use yii\console\Controller;

/**
 * История изменений (лог) моделей
 */
class HistoryController extends Controller
{
    /**
     * Установить parent_model_id
     *
     * @throws ModelValidationException
     */
    public function actionSetParentModelId()
    {
        $classToParentModelIds = [
            AccountTariffLog::className() => 'account_tariff_id',
            TariffOrganization::className() => 'tariff_id',
            TariffVoipCity::className() => 'tariff_id',
            TariffVoipNdcType::className() => 'tariff_id',
            TariffPeriod::className() => 'tariff_id',
            ClientContact::className() => 'client_id',
            // BillLine::className() => 'bill_no', // не число
        ];

        foreach ($classToParentModelIds as $class => $field) {

            $modelIdToParentModelId = [];
            /** @var HistoryChanges[][] $orphans */
            $orphans = []; // сироты

            echo PHP_EOL . $class . PHP_EOL;
            $query = HistoryChanges::find()
                ->where([
                    'model' => $class,
                    'parent_model_id' => null,
                ])
                ->orderBy(['id' => SORT_ASC]);

            /** @var HistoryChanges $historyChanges */
            foreach ($query->each() as $historyChanges) {

                // поискать в новом (insert, полном update)
                $newData = json_decode($historyChanges->data_json, true);
                if (isset($newData[$field]) && $newData[$field]) {
                    $modelIdToParentModelId[$historyChanges->model_id] = $historyChanges->parent_model_id = $newData[$field];
                }

                // поискать в старом (delete)
                if (!$historyChanges->parent_model_id) {
                    $oldData = json_decode($historyChanges->prev_data_json, true);
                    if (isset($oldData[$field]) && $oldData[$field]) {
                        $modelIdToParentModelId[$historyChanges->model_id] = $historyChanges->parent_model_id = $oldData[$field];
                    }
                }

                // поискать в кэше (обычно если частичный update)
                if (!$historyChanges->parent_model_id && isset($modelIdToParentModelId[$historyChanges->model_id])) {
                    $historyChanges->parent_model_id = $modelIdToParentModelId[$historyChanges->model_id];
                }

                // поискать в БД
                if (!$historyChanges->parent_model_id) {
                    /** @var ActiveRecord $model */
                    $model = $class::findOne(['id' => $historyChanges->model_id]);
                    if ($model) {
                        $modelIdToParentModelId[$historyChanges->model_id] = $historyChanges->parent_model_id = $model->{$field};
                    }
                }

                if (!$historyChanges->parent_model_id) {
                    // родителя так и не нашли - сирота
                    // но потом попробуем еще поискать
                    echo '- ';
                    if (!isset($orphans[$historyChanges->model_id])) {
                        $orphans[$historyChanges->model_id] = [];
                    }

                    $orphans[$historyChanges->model_id][] = $historyChanges;
                    continue;
                }

                // родителя нашли - усыновили
                echo '. ';
                if (!$historyChanges->save()) {
                    throw new ModelValidationException($historyChanges);
                }
            }

            // еще раз поискать родителей для сирот
            foreach ($orphans as $modelId => $orphan) {
                if (!isset($modelIdToParentModelId[$modelId])) {
                    // родителя так и не нашли - полная сирота
                    echo PHP_EOL . $modelId . ' ';
                    continue;
                }

                // таки родителя нашли - усыновили
                foreach ($orphan as $historyChanges) {
                    echo '+ ';
                    $historyChanges->parent_model_id = $modelIdToParentModelId[$modelId];
                    if (!$historyChanges->save()) {
                        throw new ModelValidationException($historyChanges);
                    }
                }
            }
        }

    }
}
