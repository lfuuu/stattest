<?php

namespace app\classes\behaviors;


use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\ClientBlockedComment;
use app\models\important_events\ImportantEventsNames;
use yii\base\Behavior;

class ClientBlockedCommentBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'deleteComment'
        ];
    }

    public function deleteComment()
    {
        if ($this->owner->event == ImportantEventsNames::UNSET_ZERO_BALANCE &&
            ($comment = ClientBlockedComment::findOne(['account_id' => $this->owner->client_id]))) {
            if (!$comment->delete()) {
                throw new ModelValidationException($comment);
            }
        }
    }
}