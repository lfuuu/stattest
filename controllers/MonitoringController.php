<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\monitoring\MonitorFactory;

class MonitoringController extends BaseController
{

    public function actionIndex($monitor = 'usages_lost_tariffs')
    {
        return $this->render('default', [
            'monitors' => MonitorFactory::me()->getAll(),
            'current' => MonitorFactory::me()->getOne($monitor),
        ]);
    }

}