<?php
namespace app\forms\support;

use app\classes\enum\TicketStatusEnum;
use app\models\support\TicketComment;
use app\models\Trouble;

class SubmitTicketCommentForm extends TicketCommentForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['ticket_id', 'text'], 'required'];
        return $rules;
    }

    public function save()
    {
        if ($this->validate()) {
            $item = new TicketComment();
            $item->ticket_id = $this->ticket_id;
            $item->user_id = $this->user_id;
            $item->text = $this->text;
            if ($this->saveModel($item)) {
                $this->id = $item->id;

                $this->ticket->updated_at = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::ATOM);

                if ($this->ticket->status != TicketStatusEnum::OPEN) {
                    $this->ticket->status = TicketStatusEnum::OPEN;
                    $this->ticket->save();
                    Trouble::dao()->updateTroubleBySupportTicket($this->ticket, $this->text);
                } else {
                    $this->ticket->save();
                }

                return true;
            }
        }
        return false;
    }
}