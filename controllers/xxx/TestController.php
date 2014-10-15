<?php

namespace app\controllers\xxx;

use Yii;
use app\classes\BaseController;


class TestController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}