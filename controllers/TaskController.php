<?php

namespace app\controllers;

use app\exceptions\web\NotImplementedHttpException;
use app\models\Task;
use Yii;
use yii\web\Response;
use app\classes\BaseController;

class TaskController extends BaseController
{
    public function actionIndex()
    {
        throw new NotImplementedHttpException();
    }

    public function actionGet($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $task = Task::find()
            ->where(['id' => $id])
            ->select(['progress', 'count_all', 'count_done', 'status'])
            ->createCommand()
            ->queryOne();

        return $task;
    }
}