<?php

namespace app\controllers\api;

use Yii;
use app\exceptions\FormValidationException;
use app\forms\support\TicketListForm;
use app\classes\ApiController;
use app\models\Message;

class LkCounterController extends ApiController
{
    public function actionRead()
    { 
        $accountId = Yii::$app->request->bodyParams["account_id"];

        return [
            "tickets_open" => $this->getTicketCount($accountId), 
            "messages_unread" => $this->getMessagesUnreadCount($accountId)
            ];
    }

    private function getTicketCount($accountId)
    {
        $model = new TicketListForm();
        $data = ["client_account_id" => $accountId, "status" => ["open", "done"]];

        $model->load($data, '');
        if ($model->validate()) {
            return
                (int)$model->spawnFilteredQuery()
                    ->count();
        } else {
            throw new FormValidationException($model);
        }
    }

    private function getMessagesUnreadCount($accountId)
    {
        return (int)Message::find()->where(['account_id' => $accountId, "is_read" => 0])->count();
    }
}
