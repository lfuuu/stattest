<?php

namespace app\modules\sorm\controllers;

use app\exceptions\web\NotImplementedHttpException;
use app\models\filter\SormClientFilter;
use app\models\Task;
use app\modules\sorm\filters\SormClientsFilter;
use Yii;
use yii\web\Response;
use app\classes\BaseController;

class ClientController extends BaseController
{
    public function actionIndex()
    {

        return $this->render('index',[
            'filterModel' => (new SormClientsFilter()),
        ]);
    }
}