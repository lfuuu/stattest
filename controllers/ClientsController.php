<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;


class ClientsController extends BaseController
{
    public function actionIndex()
    {
       
       // return 'sdfgsdfg';
       return  $this->render('index');
    }
}