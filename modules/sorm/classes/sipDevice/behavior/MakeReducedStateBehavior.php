<?php

namespace app\modules\sorm\classes\sipDevice\behavior;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\modules\sorm\models\SipDevice\Reduced;
use app\modules\sorm\models\SipDevice\StateLog;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;
use yii\db\Expression;

class MakeReducedStateBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => "reduceLog",
        ];
    }

    public function reduceLog(AfterSaveEvent $event)
    {
        /** @var StateLog $log */
        $log = $event->sender;

        if ($log->is_add) {
            $rState = new Reduced();
            $rState->load($log->getAttributes(), '');
            $rState->activate_dt = $log->created_at;
            if (!$rState->save()) {
                throw new ModelValidationException($rState);
            }
            return true;
        }

        // !$log->is_add

        /** @var Reduced $rState */
        $rState = Reduced::find()->where([
            'account_id' => $log->account_id,
            'did' => $log->did,
            'sip_login' => $log->sip_login,
            'activate_dt' => $log->created_at,
        ])->one();

        if (!$rState) {
            echo PHP_EOL . date('r') . ': (?) ' . $log->did . ' / ' . $log->account_id . ' / ' . $log->created_at . ' -- not found for close';
            return false;
        }

        $rState->expire_dt = new Expression('NOW()');
        if (!$rState->save()) {
            throw new ModelValidationException($rState);
        }

        return true;
    }
}