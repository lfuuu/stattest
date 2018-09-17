<?php

namespace app\modules\socket\commands;

use app\modules\socket\classes\Socket;
use ElephantIO\Exception\ServerConnectionFailureException;
use yii\console\Controller;
use yii\console\ExitCode;

class ElephantController extends Controller
{
    public $title = '';
    public $messageHtml = '';
    public $messageText = '';
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
        $options = parent::options($actionID);
        $options = array_merge($options,
            [
                Socket::PARAM_TITLE,
                Socket::PARAM_MESSAGE_HTML,
                Socket::PARAM_MESSAGE_TEXT,
                Socket::PARAM_TYPE,
                Socket::PARAM_USER_TO,
                Socket::PARAM_USER_ID_TO,
                Socket::PARAM_URL_TEXT,
                Socket::PARAM_TIMEOUT,
            ]
        );
        return $options;
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
            Socket::PARAM_MESSAGE_HTML => $this->messageHtml,
            Socket::PARAM_MESSAGE_TEXT => $this->messageText,
            Socket::PARAM_TYPE => $this->type,
            Socket::PARAM_USER_TO => $this->userTo,
            Socket::PARAM_USER_ID_TO => $this->userIdTo,
            Socket::PARAM_URL_TEXT => $this->url,
            Socket::PARAM_TIMEOUT => $this->timeout,
        ];

        try {
            return Socket::me()->emit($params) ?
                ExitCode::OK :
                ExitCode::UNSPECIFIED_ERROR;
        } catch (ServerConnectionFailureException $e) {
            echo 'Error: Сокет-сервер не запущен' . PHP_EOL;
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
