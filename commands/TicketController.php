<?php
namespace app\commands;

use app\classes\bill\ClientAccountBiller;
use app\classes\enum\TicketStatusEnum;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Payment;
use app\models\support\Ticket;
use app\models\Transaction;
use app\models\Trouble;
use yii\console\Controller;

class TicketController extends Controller
{
    public function actionCloseTicketsDoneMoreThenSevenDaysAgo()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-7 days');

        $tickets =
            Ticket::find()
                ->andWhere(['status' => TicketStatusEnum::DONE])
                ->andWhere('updated_at < :date', [':date' => $date->format(\DateTime::ATOM)])
                ->all();

        foreach ($tickets as $ticket) {
            /** @var $ticket Ticket */
            $ticket->status = TicketStatusEnum::CLOSED;
            $ticket->save();
            Trouble::dao()->updateTroubleBySupportTicket($ticket);
        }
    }
}
