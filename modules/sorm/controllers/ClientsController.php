<?php

namespace app\modules\sorm\controllers;

use app\exceptions\web\NotImplementedHttpException;
use app\models\ClientContragent;
use app\models\filter\SormClientFilter;
use app\models\Task;
use app\modules\sorm\filters\SormClientsFilter;
use Yii;
use yii\web\Response;
use app\classes\BaseController;

class ClientsController extends BaseController
{
    public function actionIndex()
    {
        throw new NotImplementedHttpException();
    }

    public function actionPerson()
    {
        return $this->render('list/person',[
            'filterModel' => (new SormClientsFilter(['type' => ClientContragent::PERSON_TYPE])),
        ]);
    }

    public function actionLegal()
    {
        return $this->render('list/legal',[
            'filterModel' => (new SormClientsFilter(['type' => ClientContragent::LEGAL_TYPE])),
        ]);
    }
}