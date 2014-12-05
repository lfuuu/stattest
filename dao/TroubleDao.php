<?php
namespace app\dao;

use app\classes\enum\ServiceTypeEnum;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\support\Ticket;
use app\models\Trouble;
use app\models\TroubleStage;
use yii\base\Exception;

/**
 * @method static TroubleDao me($args = null)
 * @property
 */
class TroubleDao extends Singleton
{
    public function getMyTroublesCount()
    {
        return
            Trouble::getDb()->createCommand("
                select count(*)
                from tt_troubles as t
                inner join tt_stages as s  on s.stage_id = t.cur_stage_id and s.trouble_id = t.id
                where s.state_id not in (2,20,21,39,40) and s.date_start<=now() and s.user_main=:userLogin
            ", [':userLogin' => \Yii::$app->user->getIdentity()->user])
                ->queryScalar();
    }

    public function createTroubleForSupportTicket($clientAccountId, $serviceType, $subject, $description, $supportTicketId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        if ($clientAccount === null) {
            throw new Exception("Client account #$clientAccountId not found");
        }

        $problem = '';
        if ($serviceType) {
          $problem .= 'Тип услуги: ' . ServiceTypeEnum::getName($serviceType) . "\n";
        }
        $problem .= 'Тема: ' . $subject . "\n";
        $problem .= $description;


        $transaction = Trouble::getDb()->beginTransaction();
        try {
            $trouble = new Trouble();
            $trouble->trouble_type = Trouble::TYPE_TROUBLE;
            $trouble->trouble_subtype = Trouble::SUBTYPE_TROUBLE;
            $trouble->client = $clientAccount->client;
            $trouble->user_author = Trouble::DEFAULT_SUPPORT_USER;
            $trouble->date_creation = (new \DateTime())->format(\DateTime::ATOM);
            $trouble->problem = $problem;
            $trouble->folder = Trouble::DEFAULT_SUPPORT_FOLDER;
            $trouble->support_ticket_id = $supportTicketId;
            $trouble->save();

            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = Trouble::DEFAULT_SUPPORT_STATE;
            $stage->user_main = Trouble::DEFAULT_SUPPORT_USER;
            $stage->date_start = (new \DateTime())->format(\DateTime::ATOM);
            $stage->date_finish_desired = $stage->date_start;
            $stage->save();

            $trouble->cur_stage_id = $stage->stage_id;
            $trouble->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    public function updateTroubleBySupportTicket(Ticket $ticket)
    {
        $trouble = Trouble::findOne(['support_ticket_id' => $ticket->id]);
        if (!$trouble) return;

        $oldStage = TroubleStage::findOne($trouble->cur_stage_id);
        if (!$oldStage) throw new Exception();

        $newStateId = $ticket->spawnTroubleStatus();
        if ($newStateId != $oldStage->state_id) {
          $oldStage->user_edit = Trouble::DEFAULT_SUPPORT_USER;
          $oldStage->save();

          $stage = new TroubleStage();
          $stage->trouble_id = $trouble->id;
          $stage->state_id = $newStateId;
          $stage->user_main = Trouble::DEFAULT_SUPPORT_USER;
          $stage->date_start = (new \DateTime())->format(\DateTime::ATOM);
          $stage->save();

          $trouble->cur_stage_id = $stage->stage_id;
          $trouble->save();
        }

    }

    public function updateSupportTicketByTrouble($troubleId)
    {
        $trouble = Trouble::findOne($troubleId);
        if (!$trouble) throw new Exception();

        if (!$trouble->support_ticket_id) {
            return;
        }



        $stage = TroubleStage::findOne($trouble->cur_stage_id);
        if (!$stage) throw new Exception();

        $ticket = Ticket::findOne($trouble->support_ticket_id);
        $ticket->setStatusByTroubleState($stage->state_id);
        $ticket->updated_at = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::ATOM);
        $ticket->save();
    }
}
