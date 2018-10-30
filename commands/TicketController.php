<?php
namespace app\commands;

use app\classes\bill\ClientAccountBiller;
use app\classes\enum\TicketStatusEnum;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Param;
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
                ->andWhere('updated_at < :date', [':date' => $date->format(DateTimeZoneHelper::DATETIME_FORMAT)])
                ->all();

        foreach ($tickets as $ticket) {
            /** @var $ticket Ticket */
            $ticket->status = TicketStatusEnum::CLOSED;
            $ticket->save();
            Trouble::dao()->updateTroubleBySupportTicket($ticket);
        }
    }

    /**
     * Пересчет кол-во траблов для "хлебных крошек" на странице по умолчанию
     *
     * @throws ModelValidationException
     */
    public function actionCheckTroubleCountRecal()
    {

        $sleep = 5;
        $countAll = 8;

        $count = 0;

        do {
            if ($count++ != 0) {
                sleep($sleep);
            }

            echo PHP_EOL . date('r');

            $param = Param::findOne(['param' => Param::IS_NEED_RECALC_TT_COUNT]);

            if ($param && $param->value) {

                echo "+";
                Trouble::dao()->getTaskFoldersCount();

                $param->value = 0;
                if (!$param->save()) {
                    throw new ModelValidationException($param);
                }
            }

        } while ($count < $countAll);
    }

}
