<?php
namespace app\forms\support;

use app\classes\enum\TicketStatusEnum;
use app\models\support\Ticket;
use app\models\support\TicketComment;
use app\models\Trouble;

class SubmitTicketForm extends TicketForm
{
    public $author;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [[
          'client_account_id', 'user_id', 'subject', 'description',
        ], 'required'];
        return $rules;
    }

    public function save()
    {
        $result = false;
        if ($this->validate()) {
            $transaction = Ticket::getDb()->beginTransaction();
            try {
                $ticket = new Ticket();
                $ticket->client_account_id = $this->client_account_id;
                $ticket->user_id = $this->user_id;
                $ticket->department = $this->department;
                $ticket->subject = $this->subject;
                $ticket->status = TicketStatusEnum::OPEN;

                if ($this->saveModel($ticket)) {
                    $this->id = $ticket->id;

                    $comment = new TicketComment();
                    $comment->ticket_id = $ticket->id;
                    $comment->user_id = $ticket->user_id;
                    $comment->text = $this->description;
                    $comment->save();

                    Trouble::dao()->createTroubleForSupportTicket(
                        $this->client_account_id,
                        $this->department,
                        $this->subject,
                        $this->description,
                        $ticket->id,
                        $this->author
                    );

                  $transaction->commit();
                  return true;
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return $result;
    }
}