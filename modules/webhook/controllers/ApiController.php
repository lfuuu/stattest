<?php
/**
 * Получение веб-хуков
 */

namespace app\modules\webhook\controllers;

use app\exceptions\ModelValidationException;
use app\exceptions\web\BadRequestHttpException;
use app\models\ClientAccount;
use app\models\Lead;
use app\models\RoistatNumberFields;
use app\models\Trouble;
use app\models\TroubleRoistat;
use app\models\TroubleState;
use app\models\User;
use app\modules\socket\classes\Socket;
use app\modules\webhook\models\ApiHook;
use app\modules\webhook\models\ContactsMessage;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\web\Controller;


/**
 * Аутентификация не нужна. Поэтому Controller, а не BaseController
 */
class ApiController extends Controller
{
    private $_content = null;
    private $_data = null;
    /** @var ClientAccount */
    private $_clientAccount = null;
    private $_clientContacts = [];
    private $_clientContactsIsOrigin = null;

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
        return [
            'status' => 'OK',
            'result' => 'skipped',
        ];

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
//                $content = json_encode([
//                    'event_type' => ApiHook::EVENT_TYPE_IN_CALLING_ANSWERED, // тип события
//                    'abon' => 265, // внутренний номер абонента ВАТС, который принимает/совершает звонок. Только если через ВАТС
//                    'did_mcn' => "74951059995",
//                    'did' => '+74959301987', // номер вызывающего/вызываемого абонента
//                    'call_id' => "12-1520008586.19367271", // ID звонка
//                    'secret' => $this->module->params['secretKey'], // секретный token, подтверждающий, что запрос пришел от валидного сервера
//                    'account_id' => '12345', // ID аккаунта MCN Telecom. Это не клиент!
//                ]);

        $this->_content = $content;

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

        $this->_data = $data;

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

        $user = User::findOne(['phone_work' => $apiHook->abon, 'enabled' => 'yes']);
        if (!$user) {
            // абонент не найден
            Yii::info('Webhook info. Абонент не найден ' . $content);
            return 'Абонент не найден';
        }

        $this->_makeContactsAndClientAccount($apiHook);

        $messageHtml = $this->_getRenderedHtmlContent($apiHook, $user);

        $params = [
            Socket::PARAM_TITLE => $apiHook->getEventTypeMessage(),
            Socket::PARAM_MESSAGE_HTML => $messageHtml,
            Socket::PARAM_MESSAGE_TEXT => $this->_htmlToText($messageHtml),
            Socket::PARAM_TYPE => $apiHook->getEventTypeStyle(),
            Socket::PARAM_USER_ID_TO => $user->id,
            Socket::PARAM_URL_TEXT => $this->_clientAccount ? $this->_clientAccount->getUrl() : '',
            Socket::PARAM_TIMEOUT => $apiHook->getEventTypeTimeout(),
        ];
        Socket::me()->emit($params);


        if ($this->_clientContacts) {
            Yii::info('Webhook ok. ' . $content);
        } else {
            Yii::info('Webhook info. Клиент не найден. ' . $content);
        }

        return 'Ok';
    }

    /**
     * Заполнение контактов и клиента
     *
     * @param ApiHook $apiHook
     */
    private function _makeContactsAndClientAccount(ApiHook $apiHook)
    {
        /**
         * Получение контактов клиента c последующим установлением
         * переменной _clientContactsIsOrigin
         *
         * @see ContactsMessage
         */
        $contactsMessage = $apiHook->getClientContacts();
        $this->_clientContacts = $contactsMessage->contacts;
        $this->_clientContactsIsOrigin = $contactsMessage->isOrigin;

        $this->_clientAccount = $clientAccount = null;
        foreach ($this->_clientContacts as $clientContact) {
            $clientAccount = $clientContact->client;
            if ($clientAccount) {
                $this->_clientAccount = $clientAccount;
                break;
            }
        }
    }

    /**
     * Преобразует HTML в текст
     *
     * @param string $html
     * @return string
     */
    private function _htmlToText($html)
    {
        $text = $html;
        $text = str_replace(PHP_EOL, '', $text); // удалить \n
        $text = str_replace(['</span>', '</tr>'], PHP_EOL, $text); // добавить \n
        $text = str_replace('>', '> ', $text);
        $text = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $text); // вырезаем <script> с содержимым
        $text = strip_tags($text);
        $text = preg_replace('/[ \t]+/', ' ', $text); // удалить дубли пробелов
        $text = html_entity_decode($text);

        return $text;
    }

    /**
     * Генерация контента на событие
     *
     * @param ApiHook $apiHook
     * @param User $user
     * @return string
     */
    private function _getRenderedHtmlContent(ApiHook $apiHook, User $user)
    {
        $messageId = $apiHook->getMessageId();

        $content = '';

        if ($apiHook->isEventTypeWithClose()) {
            $content .= $this->renderPartial('close_message_script', [
                'messageIdsForClose' => $apiHook->getMessageIdsForClose()
            ]);
        }

        $downBlock = '';
        if ($apiHook->event_type == ApiHook::EVENT_TYPE_IN_CALLING_ANSWERED && $this->module->params['is_with_lid']) {
            $this->_makeLead($messageId, $user);
            $downBlock = $this->_getLeadHtml($apiHook, $messageId);
        }

        $content .= $this->renderPartial('message', [
            'did' => $apiHook->did,
            'calling_did' => $apiHook->did_mcn,
            'abon' => $apiHook->abon,
            'clientContacts' => $this->_clientContacts,
            'messageId' => $messageId,
            'block' => ['down' => $downBlock],
            'clientContactsIsOrigin' => $this->_clientContactsIsOrigin,
        ]);

        if (!$apiHook->isEventTypesWithContent()) {
            $content .= $this->renderPartial('close_message_script', [
                'messageIdsForClose' => [$apiHook->getMessageId()]
            ]);
        }

        return $content;
    }


    /**
     * Блок лида
     *
     * @param ApiHook $apiHook
     * @param string $messageId
     * @return string
     */
    private function _getLeadHtml(ApiHook $apiHook, $messageId)
    {
        $states = TroubleState::find()
            ->where(['is_in_popup' => true])
            ->orderBy(['order' => SORT_ASC])
            ->all();

        return $this->renderPartial('lead', [
            'apiHook' => $apiHook,
            'states' => $states,
            'messageId' => $messageId,
            'clientAccount' => $this->_clientAccount,
        ]);
    }

    /**
     * Создание лида
     *
     * @param string $messageId
     * @param User $user
     * @throws \Exception
     * @internal param string $content
     */
    private function _makeLead($messageId, User $user)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $lid = Lead::findOne(['message_id' => $messageId]);

            if (!$lid) {
                $lid = new Lead();
                $lid->message_id = $messageId;
            }

            $lid->data_json = $this->_content;
            // Отдельное сохранение did, did_mcn
            if ($this->_content) {
                $data = $lid->getData();
                list($lid->did, $lid->did_mcn) = [$data['did'], $data['did_mcn']];
            }
            $lid->state_id = TroubleState::CONNECT__INCOME;
            $lid->account_id = $this->_clientAccount ? $this->_clientAccount->id : Lead::DEFAULT_ACCOUNT_ID;
            $trouble = $this->_makeLeadTrouble($lid->account_id, $user);
            $lid->trouble_id = $trouble->id;

            if (!$lid->save()) {
                throw new ModelValidationException($lid);
            }

            if ($lid->did_mcn) {
                $roistat = new TroubleRoistat();
                $roistat->trouble_id = $trouble->id;
                $roistat->roistat_visit = TroubleRoistat::getChannelNameById(TroubleRoistat::CHANNEL_PHONE);

                $roistatNumberFields = RoistatNumberFields::findOne(['number' => $lid->did_mcn]);
                if ($roistatNumberFields) {
                    $roistat->roistat_fields = $roistatNumberFields->fields;
                }

                if (!$roistat->save()) {
                    throw new ModelValidationException($roistat);
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Создание заявки для лида
     *
     * @param integer $accountId
     * @param User $user
     * @return Trouble
     */
    private function _makeLeadTrouble($accountId, User $user)
    {
        return Trouble::dao()->createTrouble(
            $accountId,
            Trouble::TYPE_CONNECT,
            Trouble::SUBTYPE_CONNECT,
            'Лид-звонок',
            $user->user);
    }

}