<?php
namespace app\commands;

use app\classes\grid\ExportGridView;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\DeferredTask;
use Exception;
use yii\console\Controller;

class DeferredTaskController extends Controller
{
    /**
     * Создать и сохранить отчет локально
     *
     * @throws ModelValidationException
     */
    public function actionProcess()
    {
        $this->_clean();

        $query = DeferredTask::find()
            ->where(['status' => DeferredTask::STATUS_WAITING_FOR_DOWNLOAD])
            ->orWhere(['AND', ['status' => DeferredTask::STATUS_IN_PROGRESS], ['NOT', ['tmp_files' => null]]])
            ->andWhere(['NOT', ['filter_model' => null]])
            ->orderBy(['created_at' => SORT_ASC]);

        foreach ($query->each() as $model) {
            /** @var DeferredTask $model */
            try {
                $model->setStatus(DeferredTask::STATUS_IN_PROGRESS);
                $params = json_decode($model->params, true);
                $filterModel = new $model->filter_model;
                $filterModel->load($params, '');
                $exportModel = new ExportGridView([
                    'filterModel' => $filterModel,
                    'statusManagerObject' => $model
                ]);
                $model->filename = $exportModel->export();
                $model->status = DeferredTask::STATUS_READY;
                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
                echo "результат задачи №{$model->id} сохранен в файл {$model->filename}" . PHP_EOL;
            } catch (Exception $e) {
                $model->setStatus(DeferredTask::STATUS_EXCEPTION);
                $model->setStatusText($e->getMessage());
                echo 'задача №' . $model->id . ': ' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    /**
     * Удалить отчеты, у которых истек срок или которые помечены на удаление
     *
     * @throws ModelValidationException
     */
    private function _clean()
    {
        $expirationDate = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->modify('-7 day')
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $expiredRecordsQuery = DeferredTask::find()
            ->where(['status' => DeferredTask::STATUS_IN_REMOVING])
            ->orWhere(['<=', 'downloaded_at', $expirationDate])
            ->orderBy(['created_at' => SORT_ASC]);

        foreach ($expiredRecordsQuery->each() as $model) {
            if (!$model->delete()) {
                throw new ModelValidationException($model);
            }
        }
    }
}