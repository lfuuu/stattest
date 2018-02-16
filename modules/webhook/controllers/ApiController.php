<?php
/**
 * Получение веб-хуков
 */

namespace app\modules\webhook\controllers;

use app\exceptions\web\BadRequestHttpException;
use app\models\User;
use app\modules\socket\classes\Socket;
use app\modules\webhook\models\ApiHook;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\web\Controller;


/**
 * Аутентификация не нужна. Поэтому Controller, а не BaseController
 */
class ApiController extends Controller
{
    /**
     * Инициализация
     */
    public function init()
    {
        $this->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * Получить hook и вернуть json
     *
     * @return array
     */
    public function actionIndex()
    {
        try {
            return [
                'status' => 'OK',
                'result' => $this->_receiveHook(),
            ];

        } catch (\Exception $e) {
            $result = $e->getMessage();
            $code = $e->getCode();

            Yii::error($e->getMessage());

            return [
                'status' => 'ERROR',
                'result' => $result,
                'code' => $code,
            ];
        }
    }

    /**
     * Получить hook
     *
     * @return string
     * @throws InvalidParamException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    private function _receiveHook()
    {
        $content = file_get_contents("php://input");

        // для дебага раскомментировать следующие строчки
        //        $content = json_encode([
        //            'event_type' => ApiHook::EVENT_TYPE_IN_CALLING_START, // тип события
        //            'abon' => 262, // внутренний номер абонента ВАТС, который принимает/совершает звонок. Только если через ВАТС
        //            'did' => '+74959319628', // номер вызывающего/вызываемого абонента
        //            'secret' => $this->module->params['secretKey'], // секретный token, подтверждающий, что запрос пришел от валидного сервера
        //            'account_id' => '12345', // ID аккаунта MCN Telecom. Это не клиент!
        //        ]);

        Yii::info('Webhook: ' . $content);
        if (!$content) {
            throw new BadRequestHttpException('Webhook error. Не указан raw body');
        }

        $params = $this->module->params;
        if (!$params['secretKey']) {
            throw new InvalidConfigException('Webhook warning. Не настроен secretKey');
        }

        $data = json_decode($content, true);
        if (!$data) {
            throw new BadRequestHttpException('Webhook error. Указан неправильный raw body ' . $content);
        }

        $apiHook = new ApiHook;
        $apiHook->setAttributes($data);
        if (!$apiHook->validate()) {
            throw new InvalidParamException('Webhook error. Указаны неправильные параметры ' . $content . '. ' . implode(' ', $apiHook->getFirstErrors()) . '#' . print_r(get_object_vars($apiHook), true));
        }

        if ($apiHook->secret != $params['secretKey']) {
            throw new InvalidParamException('Webhook error. Неправильный secretKey ' . $content);
        }

        if (!$apiHook->getIsNotify()) {
            Yii::info('Webhook info. Уведомление не требуется ' . $content);
            return 'Уведомление не требуется';
        }

        if (!$apiHook->abon) {
            // звонок на общий номер - никого уведомлять не надо
            Yii::info('Webhook info. Абонент не указан ' . $content);
            return 'Абонент не указан';
        }

        $user = User::findOne(['phone_work' => $apiHook->abon]);
        if (!$user) {
            // абонент не найден
            Yii::info('Webhook info. Абонент не найден ' . $content);
            return 'Абонент не найден';
        }

        // отправить уведомление менеджеру
        // найти контакты
        $clientContacts = $apiHook->getClientContacts();
        foreach ($clientContacts as $clientContact) {
            $clientAccount = $clientContact->client;
            if ($clientAccount) {
                break;
            }
        }

        $messageHtml = $this->renderPartial('message', [
            'did' => $apiHook->did,
            'abon' => $apiHook->abon,
            'clientContacts' => $clientContacts,
        ]);

        $messageText = $messageHtml;
        $messageText = str_replace(PHP_EOL, '', $messageText); // удалить \n
        $messageText = str_replace(['</span>', '</tr>'], PHP_EOL, $messageText); // добавить \n
        $messageText = str_replace('>', '> ', $messageText);
        $messageText = strip_tags($messageText);
        $messageText = preg_replace('/[ \t]+/', ' ', $messageText); // удалить дубли пробелов
        $messageText = html_entity_decode($messageText);

        $params = [
            Socket::PARAM_TITLE => $apiHook->getEventTypeMessage(),
            Socket::PARAM_MESSAGE_HTML => $messageHtml,
            Socket::PARAM_MESSAGE_TEXT => $messageText,
            Socket::PARAM_TYPE => $apiHook->getEventTypeStyle(),
            // Socket::PARAM_USER_TO => null,
            Socket::PARAM_USER_ID_TO => $user->id,
            Socket::PARAM_URL_TEXT => $clientAccount ? $clientAccount->getUrl() : '',
            Socket::PARAM_TIMEOUT => $apiHook->getEventTypeTimeout(),
        ];
        Socket::me()->emit($params);

        if ($clientContacts) {
            Yii::info('Webhook ok. ' . $content);
        } else {
            Yii::info('Webhook info. Клиент не найден. ' . $content);
        }

        return 'Ok';
    }
}