<?php

namespace app\forms\external_operators;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Form;
use app\models\Trouble;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\TroubleState;
use app\models\TroubleStage;
use app\classes\operators\OperatorOnlime;
use app\classes\StatModule;
use app\classes\operators\Operators;

require_once Yii::$app->basePath . '/stat/include/1c_integration.php';

class RequestOnlimeStateForm extends Form
{

    public
        $comment,
        $state_id;

    public function rules()
    {
        return [
            [['state_id'], 'required'],
            [['comment'], 'string'],
            [['state_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'state_id' => 'Состояние',
            'comment' => 'Комментарии',
        ];
    }

    public function save(Operators $operator, Bill $bill, Trouble $trouble)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($trouble->bill_no && preg_match("#\d{6}\/\d{4}#", $trouble->bill_no)) {
                $newstate = TroubleState::findOne($this->state_id);

                if ($newstate->state_1c != $bill->state_1c) {
                    $operator->saveOrderState1C($bill, $newstate);

                    if (strcmp($newstate->state_1c, 'Отказ') == 0) {
                        $bill->sum = 0;
                        $bill->sum_with_unapproved = 0;
                        $bill->state_1c = $newstate->state_1c;
                    } else {
                        $bill->state_1c = $newstate->state_1c;
                    }
                    $bill->save();
                }
            }

            $nowDateTime = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

            $currentStage = TroubleStage::findOne($trouble->currentStage->stage_id);
            $currentStage->date_edit = $nowDateTime;
            $currentStage->comment = $this->comment;
            $currentStage->user_edit = (Yii::$app->user->identity ? Yii::$app->user->identity->user : 'system');
            $currentStage->save();

            $state = TroubleState::findOne($trouble->currentStage->state_id);
            $dateStart = Yii::$app->getDb()->createCommand("
                SELECT
                    GREATEST(`date_start`, NOW()) AS date_start
                FROM
                    `tt_stages`
                WHERE
                    `stage_id` = :stage_id
            ", [
                ':stage_id' => $trouble->currentStage->stage_id
            ])->queryScalar();

            $dateFinishDesired = Yii::$app->getDb()->createCommand("
                SELECT
                    GREATEST(`date_finish_desired`, NOW() + INTERVAL :time_delta HOUR) AS date_finish_desired
                FROM
                    `tt_stages`
                WHERE
                    `trouble_id` = :trouble_id
                    AND `state_id` != 4
                ORDER BY
                    `stage_id` DESC
                LIMIT 1
            ", [
                'trouble_id' => $trouble->id,
                'time_delta' => $state->time_delta,
            ])->queryScalar();

            $stage = new TroubleStage;
            $stage->trouble_id = $trouble->id;
            $stage->state_id = $this->state_id;

            $stage->date_start = $dateStart;
            $stage->date_finish_desired = $dateFinishDesired;
            $stage->user_main = (Yii::$app->user->identity ? Yii::$app->user->identity->user : 'system');

            if (in_array($this->state_id, [2, 20, 21, 39, 40, 48])) {
                $stage->date_finish_desired = $nowDateTime;
                $stage->date_edit = $nowDateTime;
            }

            $stage->save();

            $trouble->cur_stage_id = $stage->stage_id;
            $trouble->save();

            Yii::$app->getDb()->createCommand("
                UPDATE `tt_troubles`
                SET
                    `cur_stage_id` = :stage_id,
                    `folder` = (SELECT `folder` FROM `tt_states` WHERE id = :state_id)
                    WHERE
                      `id` = :trouble_id
            ", [
                ':trouble_id' => $trouble->id,
                ':stage_id' => $stage->stage_id,
                ':state_id' => $this->state_id,
            ]);

            if (in_array($this->state_id, [2, 20, 7, 8, 48], null)) {
                Trouble::updateAll(
                    ['date_close' => $nowDateTime],
                    ['id' => $trouble->id, 'date_close' => '0000-00-00 00:00:00']
                );
            }

            Trouble::dao()->updateSupportTicketByTrouble($trouble->id);
            StatModule::tt()->checkTroubleToSend($trouble->id);

            /** TODO: переделать на bill::getDocumentType */
            if ($trouble->bill_no && $trouble->trouble_type == 'shop_orders') {
                if (in_array($this->state_id, [28, 23, 18, 7, 4, 17, 2, 20], null)) {
                    $bill->is_approved = 1;
                    $bill->sum = $bill->sum_with_unapproved;
                } else {
                    $bill->is_approved = 0;
                    $bill->sum = 0;
                }
                $bill->save();
                $bill->dao()->recalcBill($bill);
                ClientAccount::dao()->updateBalance($bill->client_id);

            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}