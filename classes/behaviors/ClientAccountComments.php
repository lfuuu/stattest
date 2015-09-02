<?php

namespace app\classes\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use app\forms\comment\ClientContractCommentForm;

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
            $comment = new ClientContractCommentForm;
            $comment->comment =
                (
                    $event->sender->is_blocked
                        ? ClientContractCommentForm::SET_CLIENT_BLOCKED_TRUE
                        : ClientContractCommentForm::SET_CLIENT_BLOCKED_FALSE
                );
            $comment->contract_id = $event->sender->contract_id;
            $comment->save();
        }

        if (
            isset($event->changedAttributes['is_active'])
            &&
            $event->changedAttributes['is_active'] != $event->sender->is_active
        ) {
            $comment = new ClientContractCommentForm;
            $comment->comment =
                (
                $event->sender->is_active
                    ? ClientContractCommentForm::SET_CLIENT_ACTIVE_TRUE
                    : ClientContractCommentForm::SET_CLIENT_ACTIVE_FALSE
                );
            $comment->contract_id = $event->sender->contract_id;
            $comment->save();
        }

        if (
            isset($event->changedAttributes['voip_disabled'])
            &&
            $event->changedAttributes['voip_disabled'] != $event->sender->voip_disabled
        ) {
            $comment = new ClientContractCommentForm;
            $comment->comment =
                (
                $event->sender->voip_disabled
                    ? ClientContractCommentForm::SET_CLIENT_VOIP_DISABLED_TRUE
                    : ClientContractCommentForm::SET_CLIENT_VOIP_DISABLED_FALSE
                );
            $comment->contract_id = $event->sender->contract_id;
            $comment->save();
        }
    }

}