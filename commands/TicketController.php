<?php
namespace app\commands;

use app\classes\enum\TicketStatusEnum;
use app\classes\helpers\TTCounterHelper;
use app\helpers\DateTimeZoneHelper;
use app\models\support\Ticket;
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
     * @throws \Exception
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

            $param = TTCounterHelper::getIsNeedRecalc();

            if ($param && $param->value) {
                $this->_recountTroubles($param->value);
                $param->setZeroVal();
            }

        } while ($count < $countAll);
    }

    /**
     * @param $val
     * @throws \Exception
     */
    private function _recountTroubles($val)
    {
        if (!$val) {
            return;
        }
        $troubleTypes = array_column(TTCounterHelper::filterTroubleData(TTCounterHelper::getTroubleTypeData(), $val), 'code');
        $troubleDao = Trouble::dao();
        foreach ($troubleTypes as $troubleType) {
            echo PHP_EOL . date('r') . "+";
            $troubleDao->getTaskFoldersCount($troubleDao::MODE_RECOUNT, $troubleType);
        }
    }
}

