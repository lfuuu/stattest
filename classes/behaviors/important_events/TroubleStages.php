<?php

namespace app\classes\behaviors\important_events;

use app\exceptions\ModelValidationException;
use app\models\TroubleStage;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTrouble;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffStatus;
use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\Trouble;
use yii\db\Expression;

class TroubleStages extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'registerAddEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @return bool
     * @throws \app\exceptions\ModelValidationException
     */
    public function registerAddEvent($event)
    {
        /**
         * @var TroubleStage $troubleStage
         * @var Trouble $trouble
         */
        $troubleStage = $event->sender;
        $trouble = Trouble::findOne($troubleStage->trouble_id);

        if (!$trouble || $trouble->stage === null) {
            return false;
        }

        /** @var ClientAccount $account*/
        $account = $trouble->account;

        if (
            $trouble->stage->state_id != $event->sender->state_id
            && !in_array($event->sender->state_id, Trouble::dao()->getClosedStatesId(), true)
        ) {
            ImportantEvents::create(ImportantEventsNames::SET_STATE_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        if ($trouble->stage->user_main != $event->sender->user_main) {
            ImportantEvents::create(ImportantEventsNames::SET_RESPONSIBLE_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        if (!empty($trouble->stage->comment)) {
            ImportantEvents::create(ImportantEventsNames::NEW_COMMENT_TROUBLE,
                ImportantEventsSources::SOURCE_STAT, [
                    'trouble_id' => $trouble->id,
                    'stage_id' => $trouble->stage->stage_id,
                    'client_id' => $account->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }

        // Создание связи между услугой и заявкой, когда состояние стало"Включено"
        if ($troubleStage->isStateEnabled()) {
            $accountTariffIds = AccountTariff::find()
                ->select('uat.id')
                ->alias('uat')
                ->leftJoin(['utp' => TariffPeriod::tableName()], 'uat.tariff_period_id = utp.id')
                ->leftJoin(['ut' => Tariff::tableName()], 'utp.tariff_id = ut.id')
                ->where([
                    'uat.client_account_id' => $account->id,
                    'uat.prev_account_tariff_id' => null,
                    'uat.service_type_id' => [
                        ServiceType::ID_VPBX,
                        ServiceType::ID_VOIP,
                        ServiceType::ID_CALL_CHAT,
                    ],
                ])
                ->andWhere(['between',
                    'uat.insert_time',
                    new Expression(':tt_date_creation - interval 1 minute' , [':tt_date_creation' => $trouble->date_creation]),
                    new Expression('NOW()')
                ])
                ->andWhere(['AND',
                    ['NOT', ['uat.tariff_period_id' => null]],
                    ['NOT', ['ut.tariff_status_id' => TariffStatus::TEST_LIST]],
                ])
                ->column();

            // Сохранение связей между услугой и заявкой
            foreach ($accountTariffIds as $accountTariffId) {
                $accountTrouble = AccountTrouble::findOne(['account_tariff_id' => $accountTariffId]);
                // Пропускаем, т.к. услуга уже закреплена за более ранним ЛИДом
                if ($accountTrouble) {
                    continue;
                }
                $accountTrouble = new AccountTrouble;
                $accountTrouble->trouble_id = $trouble->id;
                $accountTrouble->account_tariff_id = $accountTariffId;
                if (!$accountTrouble->save()) {
                    throw new ModelValidationException($accountTrouble);
                }
            }
        }
        return true;
    }
}
