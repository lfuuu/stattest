<?php
namespace app\commands;

use app\classes\adapters\EventBus;
use app\exceptions\web\NotImplementedHttpException;
use yii\console\Controller;

class EventBusController extends Controller
{
    public function actionIndex()
    {
        throw new NotImplementedHttpException();
    }

    public function actionListen()
    {
        EventBus::me()->listen();
    }

    public function actionTest()
    {
        EventBus::me()->testCmd();
    }
}
