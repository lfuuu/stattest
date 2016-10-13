<?php

namespace app\controllers\api;

use Yii;
use yii\web\BadRequestHttpException;
use app\exceptions\FormValidationException;
use app\classes\validators\AccountIdValidator;
use app\classes\DynamicModel;
use app\classes\ApiController;
use app\helpers\RenderParams;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\models\Message;
use app\models\message\Template;
use app\models\message\TemplateContent;
use app\models\message\TemplateEvents;
use app\models\Language;

class MessageController extends ApiController
{

    /**
     * @SWG\Definition(
     *   definition="message",
     *   type="object",
     *   required={"id","account_id","subject","created_at","is_read"},
     *   @SWG\Property(property="id",type="integer",description="Идентификатор сообщения"),
     *   @SWG\Property(property="account_id",type="integer",description="Идентификатор лицевого счёта"),
     *   @SWG\Property(property="subject",type="string",description="Тема сообщения"),
     *   @SWG\Property(property="created_at",type="date",description="Дата создания сообщения"),
     *   @SWG\Property(property="is_read",type="boolean",description="Прочитано ли сообщение")
     * ),
     * @SWG\Post(
     *   tags={"Работа с сообщениями"},
     *   path="/message/list/",
     *   summary="Получение списка сообщений",
     *   operationId="Получение списка сообщений",
     *   @SWG\Parameter(name="client_account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="order",type="string",description="порядок сортировки",in="formData",enum={"asc|desc"}),
     *   @SWG\Response(
     *     response=200,
     *     description="список сообщений",
     *     @SWG\Definition(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/message"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @return array
     * @throws FormValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['client_account_id', AccountIdValidator::className()],
                ['order', 'in', 'range' => ['desc', 'asc']],
            ]
        );

        if (!$form->hasErrors()) {
            $listMessages = [];
            $fromQuery = Message::find()
                ->where(['account_id' => $form->client_account_id])
                ->orderBy(['created_at' => $form->order === 'desc' ? SORT_DESC : SORT_ASC])
                ->limit(100)
                ->all();
            if ($fromQuery) {
                foreach ($fromQuery as $message) {
                    $listMessages[] = $message->toArray();
                }
            }
            return $listMessages;
        } else {
            throw new FormValidationException($form);
        }
    }

    /**
     * @SWG\Definition(
     *   definition="message_ex",
     *   type="object",
     *   required={"id","account_id","subject","created_at","is_read","text"},
     *   @SWG\Property(property="id",type="integer",description="Идентификатор сообщения"),
     *   @SWG\Property(property="account_id",type="integer",description="Идентификатор лицевого счёта"),
     *   @SWG\Property(property="subject",type="string",description="Тема сообщения"),
     *   @SWG\Property(property="created_at",type="date",description="Дата создания сообщения"),
     *   @SWG\Property(property="is_read",type="boolean",description="Прочитано ли сообщение"),
     *   @SWG\Property(property="text",type="string",description="Текст сообщения")
     * ),
     * @SWG\Post(
     *   tags={"Работа с сообщениями"},
     *   path="/message/details/",
     *   summary="Получение информации о конкретном сообщения",
     *   operationId="Получение информации о конкретном сообщения",
     *   @SWG\Parameter(name="client_account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="id",type="integer",description="идентификатор сообщения",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="сообщение",
     *     @SWG\Definition(
     *       ref="#/definitions/message_ex"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @return array|null|\yii\db\ActiveRecord
     * @throws FormValidationException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDetails()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams, [
                ['client_account_id', AccountIdValidator::className()],
                ['id', 'integer'],
                [['id'], 'required'],
            ]
        );

        if (!$form->hasErrors()) {
            $message = Message::find()
                ->where(['id' => $form->id, 'account_id' => $form->client_account_id])
                ->one();

            if ($message) {
                $messageText = $message->text->text;
                $message = $message->toArray();
                $message['text'] = $messageText;

                return $message;
            } else {
                throw new \Exception('Message not found');
            }
        } else {
            throw new FormValidationException($form);
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с сообщениями"},
     *   path="/message/read/",
     *   summary="Отметить сообщение как прочитанное",
     *   operationId="Отметить сообщение как прочитанное",
     *   @SWG\Parameter(name="client_account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="id",type="integer",description="идентификатор сообщения",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="сообщение",
     *     @SWG\Definition(
     *       ref="#/definitions/message"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @return array
     * @throws FormValidationException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRead()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams, [
                ['client_account_id', AccountIdValidator::className()],
                ['id', 'integer'],
                [['id'], 'required'],
            ]
        );

        if (!$form->hasErrors()) {
            /** @var Message $message */
            $message = Message::findOne(['id' => $form->id, 'account_id' => $form->client_account_id]);
            if (!is_null($message)) {
                if ($message->is_read == 0) {
                    $message->is_read = 1;
                    $message->save();
                }
                return $message->toArray();
            } else {
                throw new \Exception('Message not found');
            }
        } else {
            throw new FormValidationException($form);
        }
    }

    /**
     * @SWG\Get(
     *   tags={"Работа с сообщениями"},
     *   path="/message/get-template/",
     *   summary="Получение содержания шаблона почтового сообщения",
     *   operationId="Получение содержания шаблона почтового сообщения",
     *   @SWG\Parameter(name="eventCode",type="string",description="Идентификатор события",in="query",required=true),
     *   @SWG\Parameter(name="clientAccountId",type="integer",description="ID лицевого счета",in="query",required=true),
     *   @SWG\Parameter(name="type",type="string",description="Тип шаблона (по-умолчанию: email)",in="query"),
     *   @SWG\Parameter(name="eventId",type="integer",description="ID значимового события",in="query"),
     *   @SWG\Response(
     *     response=200,
     *     description="сообщение",
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @param string $eventCode
     * @param int $clientAccountId
     * @param string $type
     * @param int|null $eventId
     * @return array|bool
     * @throws BadRequestHttpException
     */
    public function actionGetTemplate(
        $eventCode,
        $clientAccountId,
        $type = Template::TYPE_EMAIL,
        $eventId = null
    ) {
        if (is_null($clientAccount = ClientAccount::findOne($clientAccountId))) {
            throw new BadRequestHttpException;
        }

        $languageCode = Language::LANGUAGE_RUSSIAN;
        if (count($clientLanguageOption = $clientAccount->getOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE))) {
            $languageCode = array_shift($clientLanguageOption);
        }

        /** @var TemplateContent $templateContent */
        $templateContentTbl = TemplateContent::tableName();
        $templateContent =
            TemplateContent::find()
                ->leftJoin([
                    'event' => TemplateEvents::tableName()
                ], 'event.template_id = ' . $templateContentTbl . '.template_id')
                ->leftJoin([
                    'template' => Template::tableName()
                ], 'template.id = ' . $templateContentTbl . '.template_id')
                ->where([
                    'event.event_code' => $eventCode,
                    $templateContentTbl . '.lang_code' => $languageCode,
                    $templateContentTbl . '.country_id' => $clientAccount->country->code,
                    $templateContentTbl . '.type' => $type
                ])
                ->one();

        if (!is_null($templateContent)) {
            switch ($type) {
                case Template::TYPE_EMAIL: {
                    $content = $templateContent->mediaManager->getFile($templateContent, true);
                    if (!empty($content)) {
                        $render = RenderParams::me();
                        return [
                            'locale' => $templateContent->lang_code,
                            'subject' => $render->apply($templateContent->title, $clientAccountId, $eventId),
                            'content' => $render->apply($content['content'], $clientAccountId, $eventId),
                        ];
                    }
                    break;
                }
                case Template::TYPE_EMAIL_INNER:
                case Template::TYPE_SMS: {
                    if (!empty($templateContent->content)) {
                        return [
                            'locale' => $templateContent->lang_code,
                            'content' => RenderParams::me()->apply($templateContent->content, $clientAccountId,
                                $eventId),
                        ];
                    }
                    break;
                }
            }
        }

        return false;
    }

}
