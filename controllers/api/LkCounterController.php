<?php

namespace app\controllers\api;

use Yii;
use app\classes\ApiController;
use app\models\Message;
use app\models\support\Ticket;

class LkCounterController extends ApiController
{
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
