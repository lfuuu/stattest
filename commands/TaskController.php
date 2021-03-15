<?php

namespace app\commands;

use app\models\Task;
use yii\console\Controller;

class TaskController extends Controller
{

    public function actionIndex()
    {
        if ($task = Task::find()->where(['status' => 'run'])->one()) {
            $task->status = 'stoped';
            $task->save();
        }

        $task = Task::find()->where(['status' => 'plan'])->one();

        if (!$task) {
            return;
        }

        $filter = new $task->filter_class;
        $filter->setAttributes(json_decode($task->filter_data_json, true), false);
        $filter->doTask($task, json_decode($task->params_json, true));
    }
}
