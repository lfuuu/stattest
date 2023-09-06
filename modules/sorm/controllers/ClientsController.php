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

    public function actionPersonB2c()
    {
        return $this->render('list/person',[
            'filterModel' => (new SormClientsFilter(['type' => ClientContragent::PERSON_TYPE, 'isB2c' => true])),
        ]);
    }

    public function actionLegalB2c()
    {
        return $this->render('list/legal',[
            'filterModel' => (new SormClientsFilter(['type' => ClientContragent::LEGAL_TYPE, 'isB2c' => true])),
        ]);
    }

    public function actionPerson()
    {
        return $this->render('list/person',[
            'filterModel' => (new SormClientsFilter(['type' => ClientContragent::PERSON_TYPE, 'isB2c' => false])),
        ]);
    }

    public function actionLegal()
    {
        return $this->render('list/legal',[
            'filterModel' => (new SormClientsFilter(['type' => ClientContragent::LEGAL_TYPE, 'isB2c' => false])),
        ]);
    }
}