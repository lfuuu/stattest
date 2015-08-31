<?php

namespace app\classes\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use app\models\ClientContractComment;

class ClientAccountComments extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'AccountIsBlocked',
        ];
    }

    public function AccountIsBlocked($event)
    {
        if (
            isset($event->changedAttributes['is_blocked'])
            &&
            $event->changedAttributes['is_blocked'] != $event->sender->is_blocked
        ) {
            $comment = new ClientContractComment;
            $comment->comment =
                (
                $event->sender->is_blocked
                    ? ClientContractComment::SET_CLIENT_BLOCKED_TRUE
                    : ClientContractComment::SET_CLIENT_BLOCKED_FALSE
                );
            $comment->user = Yii::$app->user->identity->user;
            $comment->ts = date('Y-m-d H:i:s');
            $comment->is_publish = 0;
            $comment->contract_id = $event->sender->contract_id;
            $comment->save();
        }

        if (
            isset($event->changedAttributes['is_active'])
            &&
            $event->changedAttributes['is_active'] != $event->sender->is_active
        ) {
            $comment = new ClientContractComment;

            $comment->comment =
                (
                $event->sender->is_active
                    ? ClientContractComment::SET_CLIENT_ACTIVE_TRUE
                    : ClientContractComment::SET_CLIENT_ACTIVE_FALSE
                );
            $comment->user = Yii::$app->user->identity->user;
            $comment->ts = date('Y-m-d H:i:s');
            $comment->is_publish = 0;
            $comment->contract_id = $event->sender->contract_id;
            $comment->save();
        }

        if (
            isset($event->changedAttributes['voip_disabled'])
            &&
            $event->changedAttributes['voip_disabled'] != $event->sender->voip_disabled
        ) {
            $comment = new ClientContractComment;

            $comment->comment =
                (
                $event->sender->voip_disabled
                    ? ClientContractComment::SET_CLIENT_VOIP_DISABLED_TRUE
                    : ClientContractComment::SET_CLIENT_VOIP_DISABLED_FALSE
                );
            $comment->user = Yii::$app->user->identity->user;
            $comment->ts = date('Y-m-d H:i:s');
            $comment->is_publish = 0;
            $comment->contract_id = $event->sender->contract_id;
            $comment->save();
        }
    }

}