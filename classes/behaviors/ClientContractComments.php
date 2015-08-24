<?php

namespace app\classes\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use app\models\ClientContract;
use app\models\ClientContractComment;

class ClientContractComments extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'ContractDataUpdated',
            ActiveRecord::EVENT_AFTER_INSERT => 'ContractDataUpdated',
        ];
    }

    public function ContractDataUpdated($event)
    {
        /** @var ClientContract $contract */
        $contract = $event->sender;

        if ($contract instanceof ClientContract) {
            if (
                isset($event->changedAttributes['contract_type_id'])
                    &&
                $event->changedAttributes['contract_type_id'] != $contract->contract_type_id
            ) {
                $comment = new ClientContractComment;
                $comment->comment = ClientContractComment::SET_CONTRACT_TYPE . $contract->contractType;
                $comment->user = Yii::$app->user->identity->user;
                $comment->ts = date('Y-m-d H:i:s');
                $comment->is_publish = 0;
                $comment->contract_id = $contract->id;
                $comment->save();
            }

            if (
                isset($event->changedAttributes['business_process_id'])
                    &&
                $event->changedAttributes['business_process_id'] != $contract->business_process_id
            ) {
                $comment = new ClientContractComment;
                $comment->comment = ClientContractComment::SET_BUSINESS_PROCESS . $contract->businessProcess;
                $comment->user = Yii::$app->user->identity->user;
                $comment->ts = date('Y-m-d H:i:s');
                $comment->is_publish = 0;
                $comment->contract_id = $contract->id;
                $comment->save();
            }

            if (
                isset($event->changedAttributes['business_process_status_id'])
                    &&
                $event->changedAttributes['business_process_status_id'] != $contract->business_process_status_id
            ) {
                $comment = new ClientContractComment;
                $comment->comment = ClientContractComment::SET_BUSINESS_PROCESS_STATUS . $contract->businessProcessStatus;
                $comment->user = Yii::$app->user->identity->user;
                $comment->ts = date('Y-m-d H:i:s');
                $comment->is_publish = 0;
                $comment->contract_id = $contract->id;
                $comment->save();
            }
        }
    }

}
