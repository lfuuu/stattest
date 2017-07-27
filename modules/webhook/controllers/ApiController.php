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
        $messages = [];
        $messages[] = $apiHook->did . ' -> ' . $apiHook->abon;
        if ($clientAccount = $apiHook->clientAccount) {
            $messages[] = '';
            $messages[] = $clientAccount->getNameAndContacts(PHP_EOL);
        }

        $message = implode(PHP_EOL, $messages);

        $params = [
            Socket::PARAM_TITLE => $apiHook->getEventTypeMessage(),
            Socket::PARAM_MESSAGE => $message,
            Socket::PARAM_TYPE => $apiHook->getEventTypeStyle(),
            // Socket::PARAM_USER_TO => null,
            Socket::PARAM_USER_ID_TO => $user->id,
            Socket::PARAM_URL => $clientAccount ? $clientAccount->getUrl() : '',
            Socket::PARAM_TIMEOUT => $apiHook->getEventTypeTimeout(),
        ];
        Socket::me()->emit($params);

        if ($clientAccount) {
            Yii::info('Webhook ok. ' . $content);
        } else {
            Yii::info('Webhook info. Клиент не найден. ' . $content);
        }

        return 'Ok';
    }
}