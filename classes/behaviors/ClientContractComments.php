<?php

namespace app\classes\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use app\models\ClientContract;
use app\forms\comment\ClientContractCommentForm;

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
                isset($event->changedAttributes['business_id'])
                    &&
                $event->changedAttributes['business_id'] != $contract->business_id
            ) {
                $comment = new ClientContractCommentForm;
                $comment->comment = ClientContractCommentForm::SET_BUSINESS . $contract->business;
                $comment->contract_id = $contract->id;
                $comment->save();
            }

            if (
                isset($event->changedAttributes['business_process_id'])
                    &&
                $event->changedAttributes['business_process_id'] != $contract->business_process_id
            ) {
                $comment = new ClientContractCommentForm;
                $comment->comment = ClientContractCommentForm::SET_BUSINESS_PROCESS . $contract->businessProcess;
                $comment->contract_id = $contract->id;
                $comment->save();
            }

            if (
                isset($event->changedAttributes['business_process_status_id'])
                    &&
                $event->changedAttributes['business_process_status_id'] != $contract->business_process_status_id
            ) {
                $comment = new ClientContractCommentForm;
                $comment->comment = ClientContractCommentForm::SET_BUSINESS_PROCESS_STATUS . $contract->businessProcessStatus->name;
                $comment->contract_id = $contract->id;
                $comment->save();
            }
        }
    }

}
