<?php

namespace app\controllers\api;

use app\models\message\TemplateContent;
use Yii;
use app\classes\ApiController;
use app\models\Message;
use app\classes\validators\AccountIdValidator;
use app\classes\DynamicModel;
use app\exceptions\FormValidationException;
use app\helpers\RenderParams;

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
                            ->orderBy(['created_at' => $form->order == 'desc'  ? SORT_DESC : SORT_ASC])
                            ->limit(100)
                            ->all();
            if ($fromQuery)
            {
                foreach($fromQuery as $message)
                {
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

            if ($message)
            {
                $messageText = $message->text->text;
                $message = $message->toArray();
                $message["text"] = $messageText;

                return $message;
            } else {
                throw new \Exception("Message not found");
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
            $msg = Message::findOne(['id' => $form->id, 'account_id' => $form->client_account_id]);
            if ($msg) {
                if ($msg->is_read == 0) {
                    $msg->is_read = 1;
                    $msg->save();
                }
                return $msg->toArray();
            } else {
                throw new \Exception('Message not found');
            }
        } else {
            throw new FormValidationException($form);
        }
    }

    /**
     * @param int $templateId
     * @param string $langCode
     * @param int $clientAccountId
     * @param int $contactId
     * @param string $type
     * @param int|null $eventId
     */
    public function actionEmailTemplateContent($templateId, $langCode, $clientAccountId, $contactId, $type='email', $eventId = null)
    {
        /** @var TemplateContent $templateContent */
        $templateContent = TemplateContent::findOne([
            'template_id' => $templateId,
            'lang_code' => $langCode,
            'type' => $type
        ]);

        if (!is_null($templateContent)) {
            switch ($type) {
                case 'email': {
                    $content = $templateContent->getMediaManager()->getFile($templateContent, true);
                    echo RenderParams::me(['clientAccountId' => $clientAccountId])->apply($content['content'], $clientAccountId, $contactId, $eventId);
                    break;
                }
                case 'sms': {
                    echo RenderParams::me()->apply($templateContent->content, $clientAccountId, $contactId, $eventId);
                    break;
                }
            }
            exit(0);
        }
    }

}
