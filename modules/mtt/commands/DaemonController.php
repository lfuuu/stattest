<?php

namespace app\modules\mtt\commands;

use app\modules\mtt\classes\MttAdapter;
use yii\console\Controller;

class DaemonController extends Controller
{
    /**
     * Слушать очередь и получать ответы
     *
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     * @throws \PhpAmqpLib\Exception\AMQPOutOfBoundsException
     */
    public function actionRun()
    {
        MttAdapter::me()->runReceiverDaemon();

        echo PHP_EOL;
        return Controller::EXIT_CODE_NORMAL;
    }
}
