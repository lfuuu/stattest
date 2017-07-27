<?php

namespace app\modules\socket\commands;

use app\modules\socket\classes\Socket;
use ElephantIO\Exception\ServerConnectionFailureException;
use yii\console\Controller;

class ElephantController extends Controller
{
    public $title = '';
    public $message = '';
    public $type = Socket::PARAM_TYPE_DEFAULT;
    public $userTo = null;
    public $userIdTo = null;
    public $url = null;
    public $timeout = 0;

    /**
     * Список возможных параметров при вызове метода
     *
     * @param string $actionID
     * @return string[]
     */
    public function options($actionID)
    {
        return [Socket::PARAM_TITLE, Socket::PARAM_MESSAGE, Socket::PARAM_TYPE, Socket::PARAM_USER_TO, Socket::PARAM_USER_ID_TO, Socket::PARAM_URL, Socket::PARAM_TIMEOUT];
    }

    /**
     * Эмитирировать событие для сокет-сервера
     * Описание с примерами см. в readme.md
     *
     * @return int
     */
    public function actionIndex()
    {
        $params = [
            Socket::PARAM_TITLE => $this->title,
            Socket::PARAM_MESSAGE => $this->message,
            Socket::PARAM_TYPE => $this->type,
            Socket::PARAM_USER_TO => $this->userTo,
            Socket::PARAM_USER_ID_TO => $this->userIdTo,
            Socket::PARAM_URL => $this->url,
            Socket::PARAM_TIMEOUT => $this->timeout,
        ];

        try {
            return Socket::me()->emit($params) ?
                Controller::EXIT_CODE_NORMAL :
                Controller::EXIT_CODE_ERROR;
        } catch (ServerConnectionFailureException $e) {
            echo 'Error: Сокет-сервер не запущен' . PHP_EOL;
            return Controller::EXIT_CODE_ERROR;
        }
    }
}
