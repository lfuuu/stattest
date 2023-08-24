<?php

namespace app\modules\sorm\controllers;

use app\exceptions\web\NotImplementedHttpException;
use app\models\filter\SormClientFilter;
use app\models\Task;
use app\modules\sorm\filters\SormClientsFilter;
use app\modules\sorm\models\pg\Address;
use Yii;
use yii\web\Response;
use app\classes\BaseController;

class AddressController extends BaseController
{
    public function actionIndex($hash)
    {
        return $this->render('index',[
            'hash' => $hash,
            'model' => Address::find()->where(['hash' => $hash])->one(),
        ]);
    }
}