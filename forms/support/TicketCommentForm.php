<?php
namespace app\forms\support;

use app\classes\Form;
use app\classes\validators\TicketIdValidator;
use app\models\support\Ticket;

class TicketCommentForm extends Form
{
    public $id;
    public $ticket_id;
    public $user_id;
    public $text;
    /** @var  Ticket */
    public $ticket;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['ticket_id'], TicketIdValidator::className(), 'ticket' => 'ticket'],
            [['user_id'], 'string', 'length' => 24],
            [['text'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'text' => 'Комментарий',
        ];
    }
}