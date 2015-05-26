<?php

namespace app\controllers\api;

use Yii;
use app\classes\ApiController;
use app\models\Message;
use app\classes\validators\AccountIdValidator;
use app\classes\DynamicModel;
use app\exceptions\FormValidationException;

class MessageController extends ApiController
{

    public function actionList()
    {
        $form = DynamicModel::validateData(
                        Yii::$app->request->bodyParams, 
                        [
                            ['client_account_id', AccountIdValidator::className()],
                            ['order', 'in', 'range' => ['desc', 'asc']],
                            [['client_account_id'], 'required'],
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

    public function actionDetails()
    {
        $form = DynamicModel::validateData(
                        Yii::$app->request->bodyParams, [
                            ['client_account_id', AccountIdValidator::className()],
                            ['id', 'integer'],
                            [['client_account_id', 'id'], 'required'],
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

    public function actionRead()
    {
        $form = DynamicModel::validateData(
                        Yii::$app->request->bodyParams, [
                            ['client_account_id', AccountIdValidator::className()],
                            ['id', 'integer'],
                            [['client_account_id', 'id'], 'required'],
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
            throw new FormValidationException($model);
        }
    }

}
