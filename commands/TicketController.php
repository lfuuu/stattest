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
use app\models\TroubleType;
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
     * @param $isAll 1 - пересчет всего, 0 - рассчитывает только таски
     * @throws ModelValidationException
     */
    public function actionCheckTroubleCountRecal($isAll = 1)
    {
        $sleep = 5;
        $countAll = 8;

        $count = 0;

        $troubleTypes = array_keys(Trouble::$types);
        $arr = [];
        foreach ($troubleTypes as $troubleType) {
            $folder = TroubleType::find()->select('folders')->where(['code' => $troubleType])->scalar();
            if (!$folder) {
                continue;
            }
            $arr[$troubleType] = $folder;
        }

        do {
            if ($count++ != 0) {
                sleep($sleep);
            }

            echo PHP_EOL . date('r');

            $param = Param::findOne(['param' => Param::IS_NEED_RECALC_TT_COUNT]);

            if ($param && $param->value) {
                $this->_recountTroubles($isAll, $arr);
                $param->setZeroVal();
            }

        } while ($count < $countAll);
    }

    /**
     * @param $isAll 1 - пересчет всего, 0 - рассчитывает только таски
     * @param $data
     */
    private function _recountTroubles($isAll, $data)
    {
        if (!$isAll) {
            echo PHP_EOL . date('r') . "+";
            Trouble::dao()->getTaskFoldersCount();
            return;
        }
        foreach ($data as $troubleType => $folder) {
            echo PHP_EOL . date('r') . "+";
            Trouble::dao()->getTaskFoldersCount(false, $troubleType, $folder);
        }
    }

}

