<?php
namespace app\dao;

use \yii\db\Expression;
use app\classes\Assert;
use app\classes\enum\DepartmentEnum;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\support\Ticket;
use app\models\Trouble;
use app\models\TroubleStage;
use app\models\User;
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
            Trouble::find()
                ->from(['t' => 'tt_troubles'])
                ->innerJoin(['s' => 'tt_stages'], 's.stage_id = t.cur_stage_id and s.trouble_id = t.id')
                ->where(['s.user_main' => \Yii::$app->user->getIdentity()->user])
                ->andWhere(['<=', 's.date_start', new Expression('now()')])
                ->andWhere(['not in', 's.state_id', $this->getClosedStatesId()])
                ->count();
    }

    public function getServerTroublesIDsForClient(ClientAccount $client)
    {
        return
            Trouble::find()
                ->select('tt.id')
                ->from(['tt' => 'tt_troubles'])
                ->innerJoin(['ts' => 'tt_stages'], 'ts.stage_id = tt.cur_stage_id AND ts.trouble_id = tt.id')
                ->innerJoin(['pbx' => 'server_pbx'], 'pbx.id = tt.server_id')
                ->innerJoin(['dc' => 'datacenter'], 'dc.id = pbx.datacenter_id')
                ->innerJoin(['c' => 'clients'], 'dc.region = c.region')
                ->where(
                    [
                        'and',
                        ['>', 'tt.`server_id`', 0],
                        ['not in', 'ts.`state_id`', $this->getClosedStatesId()],
                        ['=', 'c.`client`', $client->client]
                    ]
                )->createCommand()->queryAll();
    }

    public function createTroubleForSupportTicket(
        $clientAccountId,
        $department,
        $subject,
        $description,
        $supportTicketId,
        $author = false
    ) {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        Assert::isObject($clientAccount);

        $problem = '';
        if ($department) {
            $problem .= 'Отдел: ' . DepartmentEnum::getName($department) . "\n";
        }
        $problem .= 'Тема: ' . $subject . "\n";
        $problem .= $description;

        $supportUser = $this->getUserByDepartment($department);

        $transaction = Trouble::getDb()->beginTransaction();
        try {
            $trouble = new Trouble();
            $trouble->trouble_type = Trouble::TYPE_TROUBLE;
            $trouble->trouble_subtype = Trouble::SUBTYPE_TROUBLE;
            $trouble->client = $clientAccount->client;
            $trouble->user_author = $author ?: $supportUser;
            $trouble->date_creation = (new \DateTime())->format(\DateTime::ATOM);
            $trouble->problem = $problem;
            $trouble->folder = Trouble::DEFAULT_SUPPORT_FOLDER;
            $trouble->support_ticket_id = $supportTicketId;
            $trouble->save();

            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = Trouble::DEFAULT_SUPPORT_STATE;
            $stage->user_main = $supportUser;
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
        if (!$trouble) {
            return;
        }

        $supportUser = $this->getUserByDepartment($ticket->department);

        $oldStage = TroubleStage::findOne($trouble->cur_stage_id);
        Assert::isObject($oldStage);

        $newStateId = $ticket->spawnTroubleStatus();
        if ($newStateId != $oldStage->state_id) {
            $oldStage->user_edit = $supportUser;
            $oldStage->save();

            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = $newStateId;
            $stage->user_main = $supportUser;
            $stage->date_start = (new \DateTime())->format(\DateTime::ATOM);
            $stage->save();

            $trouble->cur_stage_id = $stage->stage_id;
            $trouble->save();
        }

    }

    public function updateSupportTicketByTrouble($troubleId)
    {
        $trouble = Trouble::findOne($troubleId);
        Assert::isObject($trouble);

        if (!$trouble->support_ticket_id) {
            return;
        }

        $stage = TroubleStage::findOne($trouble->cur_stage_id);
        Assert::isObject($stage);

        $ticket = Ticket::findOne($trouble->support_ticket_id);
        $ticket->setStatusByTroubleState($stage->state_id);
        $ticket->updated_at = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::ATOM);
        $ticket->save();
    }

    private function getUserByDepartment($department)
    {
        if ($department == DepartmentEnum::SALES) {
            return Trouble::DEFAULT_SUPPORT_SALES;
        } elseif ($department == DepartmentEnum::ACCOUNTING) {
            return Trouble::DEFAULT_SUPPORT_ACCOUNTING;
        } elseif ($department == DepartmentEnum::TECHNICAL) {
            return Trouble::DEFAULT_SUPPORT_TECHNICAL;
        } else {
            return Trouble::DEFAULT_SUPPORT_USER;
        }
    }

    public function addStage($trouble, $newStateId, $comment, $newUserMainId = null, $userEditId = null)
    {
        if (!$userEditId) {
            $userEdit = \Yii::$app->user->getIdentity();
        } else {
            $userEdit = User::findOne(["id" => $userEditId]);
        }

        $userMain = null;
        if ($newUserMainId) {
            $userMain = User::findOne(["id" => $newUserMainId]);
        }

        Assert::isObject($userEdit);
        Assert::isObject($trouble);

        $curStage = $trouble->currentStage;
        Assert::isObject($curStage);

        $curStage->user_edit = $userEdit->user;

        if (trim($comment)) {
            $curStage->comment = $comment;
        }

        $curStage->save();

        $stage = new TroubleStage();
        $stage->trouble_id = $trouble->id;
        $stage->state_id = $newStateId;
        $stage->user_main = $userMain ? $userMain->user : $curStage->user_main;
        $stage->date_start = (new \DateTime())->format(\DateTime::ATOM);
        $stage->save();

        $trouble->cur_stage_id = $stage->stage_id;
        $trouble->save();
        return $stage;
    }

    public function getClosedStatesId()
    {
        return [
            2,  //Закрыт
            20, //Закрыт
            21, //Отказ
            39, //Закрыт
            40, //Отказ
            45, //Техотказ
            46, //Отказ
            47, //Мусор
            48, //Включено
        ];
    }
}
