<?php

namespace app\classes\sender;

use app\classes\HttpClient;
use app\classes\Singleton;
use app\models\User;
use yii\base\InvalidConfigException;
use yii\httpclient\Request;

/**
 * Class RocketChat
 *
 * @method static RocketChat me($args = null)
 */
class RocketChat extends Singleton
{
    /**
     * Отправка оповещения о назначении траблы
     *
     * @param array $param
     * @return bool
     */
    public function sendTroubleNotifier($param)
    {
        if (!isset($param['text']) || !$param['text'] || !\Yii::$app->params['rocket_chat_token']) {
            return false;
        }

        /** @var User $user */
        $user = User::findByUsername($param['user']);

        if (!$user || !$user->rocket_nick) {
            return false;
        }

        $param['text'] .= "\n" . \Yii::$app->params['SITE_URL'] . "?module=tt&action=view&id=" . $param['trouble_id'];

        $this->send($user->rocket_nick, $param['text']);
    }

    public function send($rocketNick, $msg)
    {
        $token = \Yii::$app->params['rocket_chat_token'];

        if (!$token) {
            throw new InvalidConfigException('Rocket.Chat token не задан');
        }

        $msg = str_replace("&nbsp;", " ", $msg);
        $msg = str_replace("&amp;", "&", $msg);
        $msg = str_replace(["#171;", "#187;", "&quot;"], "\"", $msg);

        $headers = [
            'X-Auth-Token' => $token,
        ];

        $data = ['channel' => '@' . $rocketNick, 'text' => $msg];

        $responce = (new HttpClient())
            ->createJsonRequest()
            ->addHeaders($headers)
            ->setMethod('post')
            ->setData($data)
            ->setUrl('https://chat.mcn.ru/hooks/' . $token)
            ->setIsCheckOk(false)
            ->send();

        if (!$responce->data) {
            throw new \LogicException('непонятный ответ');
        }

        if (!$responce->data['success']) {
            throw new \LogicException($responce->data['error']);
        }

        return true;
    }
}