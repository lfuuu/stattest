<?php

namespace app\modules\socket\classes;

use app\classes\BaseView;
use app\classes\Singleton;
use app\models\User;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;
use kartik\base\Config;
use Yii;
use yii\web\JsExpression;

/**
 * @method static Socket me($args = null)
 */
class Socket extends Singleton
{
    // многие из этих констант используются не только в php/js, но в node.js
    const EVENT = 'message'; // Название события

    const HANDSHAKE_USER = 'user'; // Логин отправителя (user_users.user). Обязательно
    const HANDSHAKE_USER_ID = 'userId'; // ID отправителя (user_users.id). Обязательно
    const HANDSHAKE_SIG = 'sig'; // Сигнатура с помощью секретного ключа. Добавляется автоматически

    const PARAM_TITLE = 'title'; // Заголовок. Обязательное поле
    const PARAM_MESSAGE_HTML = 'messageHtml'; // HTML-сообщение
    const PARAM_MESSAGE_TEXT = 'messageTxt'; // TEXT-сообщение
    const PARAM_TYPE = 'type'; // Тип стиля: warning/success/info/danger. По умолчанию self::PARAM_TYPE_DEFAUL.
    const PARAM_USER_FROM = 'userFrom'; // Логин отправителя (user_users.user). Добавляется автоматически
    const PARAM_USER_ID_FROM = 'userIdFrom'; // ID отправителя (user_users.id). Добавляется автоматически
    const PARAM_USER_TO = 'userTo'; // Логин адресата (user_users.user). Не обязательно. Если указан логин или ID - отправляется этому адресату, иначе всем
    const PARAM_USER_ID_TO = 'userIdTo'; // Логин адресата (user_users.id). Не обязательно. Если указан логин или ID - отправляется этому адресату, иначе всем
    const PARAM_URL_TEXT = 'url'; // Ссылка для TEXT-сообщения. Не обязательно. Можно как абсолютную, так и относительную. А для HTML-сообщения ссылку можно запихнуть в HTML
    const PARAM_TIMEOUT = 'timeout'; // Через сколько миллисекунд скрыть. Если не указано или 0 - не скрывать

    const PARAM_TYPE_DEFAULT = 'success'; // Значение по умолчанию для self::PARAM_TYPE. Возможные варианты: warning/success/info/danger

    protected $module = null;

    /**
     * Вывести необходимые скрипты для работы с сокетами
     *
     * @return bool
     */
    public function echoScript()
    {
        $this->module = Config::getModule('socket');

        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        // параметры "рукопожатия"
        $handshakeData = [
            self::HANDSHAKE_USER => $user->user,
            self::HANDSHAKE_USER_ID => $user->id,
        ];

        $url = $this->getUrl($handshakeData);
        if (!$url) {
            return false;
        }

        /** @var BaseView $baseView */
        $baseView = $this->module->module->getView();

        $baseView->registerJs(new JsExpression('window.ioUrl = "' . $url . '"'), BaseView::POS_BEGIN); // позиция должна быть выше js-файлов

        $assets = dirname(__FILE__) . '/../assets';
        list($serverUrl, $siteUrl) = Yii::$app->assetManager->publish($assets);
        $baseView->registerCssFile($siteUrl . '/socket.css');
        $baseView->registerJsFile($siteUrl . '/socket.io.min.js', ['position' => BaseView::POS_END]); // $this->module->params['url'] . '/socket.io/socket.io.js'
        if ($_SERVER['IS_WITH_COMET'] ?? false) {
            $baseView->registerJsFile($siteUrl . '/socket.js', ['position' => BaseView::POS_END]);
        }

        return true;
    }

    /**
     * Отправить сообщение через сокет-сервер
     *
     * @param array $params Описание см. в self::PARAM_*
     * @return bool
     */
    public function emit($params)
    {
        Yii::info('Emit: ' . print_r($params, true));

        $this->module = Config::getModule('socket');

        // параметры "рукопожатия"
        $handshakeData = [
            self::HANDSHAKE_USER => User::SYSTEM_USER,
            self::HANDSHAKE_USER_ID => User::SYSTEM_USER_ID,
        ];

        $url = $this->getUrl($handshakeData, true);
        if (!$url) {
            return false;
        }

        $engine = new Version2X($url);
        $client = new Client($engine);
        $client->initialize();
        $client->emit(self::EVENT, $params);
        $client->close();

        return true;
    }

    /**
     * Вернуть URL сокет-сервера
     *
     * @param array $handshakeData Должны быть ключи user и user_id
     * @param bool $isBackEnd получить url для бэка
     * @return string
     */
    protected function getUrl($handshakeData, $isBackEnd = false)
    {
        $params = $this->module->params;

        $url = $params['url'];
        if ($isBackEnd && !empty($params['backend_url'])) {
            $url = $params['backend_url'];
        }

        $secretKey = $params['secretKey'];

        if (!$url || !$secretKey) {
            return '';
        }

        $handshakeData[self::HANDSHAKE_SIG] = $this->_generateSig($handshakeData, $secretKey); // подписать
        $url .= '/?' . http_build_query($handshakeData, null, $arg_separator = '&', $enc_type = PHP_QUERY_RFC3986 /* node.js вместо пробела хочет "%20", а не "+" */);

        return $url;
    }

    /**
     * Вернуть подпись (сигнатуру)
     *
     * @param array $handshakeData
     * @param string $secretKey
     * @return string
     */
    private function _generateSig($handshakeData, $secretKey)
    {
        $paramsStr = '';
        foreach ($handshakeData as $k => $v) {
            $paramsStr .= $k . '=' . $v;
        }

        return md5($paramsStr . $secretKey);
    }
}