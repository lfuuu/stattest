<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;


class TestController extends BaseController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}