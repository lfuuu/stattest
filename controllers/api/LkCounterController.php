<?php

namespace app\controllers\api;

use Yii;
use app\classes\ApiController;
use app\models\Message;
use app\models\support\Ticket;

class LkCounterController extends ApiController
{
    /**
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/lk-counter/read/",
     *   summary="Получение информации о тикетах и непрочитанных сообщениях",
     *   operationId="Получение информации о тикетах и непрочитанных сообщениях",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="информация о лицевом счёте",
     *     @SWG\Definition(
     *       type="object",
     *       required={"tickets_unread","messages_unread"},
     *       @SWG\Property(property="tickets_unread",type="integer",description="количество тикетов"),
     *       @SWG\Property(property="messages_unread",type="integer",description="количество сообщений"),
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
        $accountId = Yii::$app->request->bodyParams["account_id"];

        return [
            "tickets_unread" => $this->getTicketCount($accountId),
            "messages_unread" => $this->getMessagesUnreadCount($accountId)
        ];
    }

    private function getTicketCount($accountId)
    {
        return (int)Ticket::find()->where(["client_account_id" => $accountId, "is_with_new_comment" => 1])->count();
    }

    private function getMessagesUnreadCount($accountId)
    {
        return (int)Message::find()->where(['account_id' => $accountId, "is_read" => 0])->count();
    }
}
