<?php

namespace app\controllers\voipreport;

use app\classes\BaseController;
use app\classes\grid\ExportGridView;
use app\exceptions\ModelValidationException;
use app\models\DeferredTask;
use Yii;

class DeferredTaskController extends BaseController
{
    /**
     * Создание отложенной загрузки отчета
     *
     * @return \yii\web\Response
     * @throws ModelValidationException
     * @throws \Throwable
     */
    public function actionNew()
    {
        $get = Yii::$app->request->get();
        if (!$get || !isset($get['CallsRawFilter']) ||
            empty(array_filter($get['CallsRawFilter'], function($value) {
                return $value !== '' && $value !== null;
            }))) {
            throw new \InvalidArgumentException('Заполните фильтр');
        }

        $model = new DeferredTask();
        $model->params = json_encode($get['CallsRawFilter']);
        $model->user_id = Yii::$app->user->getIdentity()->getId();
        $model->status = DeferredTask::STATUS_WAITING_FOR_DOWNLOAD;
        $model->scenario = 'insert';

        if (!$model->save()) {
            \Yii::$app->response->statusCode = 500;
            $errorString = '';
            foreach ($model->getErrors() as $error) {
                $errorString .= implode(";\n", $error) . "\n";
            }
            \Yii::$app->response->content = $errorString;
        } else {
            \Yii::$app->response->statusCode = 200;
        }
    }

    /**
     * Скачать отчет
     *
     * @param $id
     * @return \yii\web\Response
     * @throws \yii\base\ExitException
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionDownload($id)
    {
        $model = DeferredTask::findOne($id);
        $path = ExportGridView::getPath();
        if (!$model ||
            $model->status != DeferredTask::STATUS_READY ||
            !$model->filename ||
            !file_exists($path . $model->filename)) {

            Yii::$app->session->setFlash('error', 'При загрузке отчета возникла ошибка.');
            return $this->redirect(['voipreport/calls/trunc']);
        }
        Yii::$app->response->sendFile($path . $model->filename, $model->filename);
    }

    /**
     * Поставить отчет на удаление
     *
     * @param $id
     * @return \yii\web\Response
     * @throws ModelValidationException
     */
    public function actionRemove($id)
    {
        $model = DeferredTask::findOne($id);
        if (!$model || $model->status == DeferredTask::STATUS_IN_PROGRESS) {
            Yii::$app->session->setFlash('error', 'При удалении отчета возникла ошибка.');
            return $this->redirect(Yii::$app->request->referrer);
        }
        $model->setStatus(DeferredTask::STATUS_IN_REMOVING);
        return $this->redirect(Yii::$app->request->referrer);
    }
}
