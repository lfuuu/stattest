<?php

use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\LkClientSettings;
use app\models\LkNoticeSetting;

class m160516_171448_important_event_day_limit extends \app\classes\Migration
{
    public function up()
    {
        $this->update(ImportantEvents::tableName(), [
            'event' => 'min_day_limit'
        ], [
            'event' => 'day_limit'
        ]);

        $this->update(ImportantEventsNames::tableName(), [
            'code' => 'min_day_limit',
            'value' => 'Минимальный суточный лимит'
        ], [
                'code' => 'day_limit'
            ]
        );

        if(!(\app\models\important_events\ImportantEventsGroups::findOne(['id' => 9]))) {
            $this->insert(\app\models\important_events\ImportantEventsGroups::tableName(), [
                "id" => 9,
                "title" => "Услуга телефония"
            ]);
        }

        $this->insert(ImportantEventsNames::tableName(), [
            'code' => 'unset_min_day_limit',
            'value' => 'Снятие: Минимальный суточны лимит',
            'group_id' => 9
        ]);

        $this->insert(ImportantEventsNames::tableName(), [
            'code' => 'day_limit',
            'value' => 'Блокировка по достижению суточного лимита',
            'group_id' => 9
        ]);
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => 'unset_day_limit',
            'value' => 'Снятие блокировки по суточному лимиту',
            'group_id' => 9
        ]);

        $this->execute("update important_events i, important_events_properties p set event = concat('unset_', event) where i.id = p.event_id and p.property='is_set' and value =0");

        $this->renameColumn(LkClientSettings::tableName(), 'day_limit', 'min_day_limit');
        $this->renameColumn(LkClientSettings::tableName(), 'day_limit_sent', 'min_day_limit_sent');
        $this->renameColumn(LkClientSettings::tableName(), 'is_day_limit_sent', 'is_min_day_limit_sent');

        $this->renameColumn(LkNoticeSetting::tableName(), 'day_limit', 'min_day_limit');

        $this->addColumn(LkClientSettings::tableName(), 'day_limit_sent', $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'));
        $this->addColumn(LkClientSettings::tableName(), 'is_day_limit_sent', $this->integer(4)->notNull()->defaultValue(0));

        $this->alterColumn(\app\models\LkNotificationLog::tableName(), 'event', "enum('add_pay_notif','day_limit','zero_balance','prebil_prepayers_notif','min_balance', 'min_day_limit') DEFAULT NULL");
        $this->update(\app\models\LkNotificationLog::tableName(), ['event' => 'min_day_limit'], ['event' => 'day_limit']);
    }

    public function down()
    {
        $this->delete(ImportantEventsNames::tableName(), ['code' => ['unset_day_limit', 'day_limit', 'unset_min_day_limit']]);
        $this->update(ImportantEvents::tableName(), ['event' => 'day_limit'], ['event' => 'min_day_limit']);
        $this->update(ImportantEventsNames::tableName(), ['code' => 'day_limit', 'value' => 'Блокировка по достижению суточного лимита'], ['code' => 'min_day_limit']);
        $this->execute("update important_events i, important_events_properties p set event = replace(event, 'unset_', '') where i.id = p.event_id and p.property='is_set' and value =0 and event like 'unset_%'");

        $this->dropColumn(LkClientSettings::tableName(), 'day_limit_sent');
        $this->dropColumn(LkClientSettings::tableName(), 'is_day_limit_sent');

        $this->renameColumn(LkClientSettings::tableName(), 'min_day_limit', 'day_limit');
        $this->renameColumn(LkClientSettings::tableName(), 'min_day_limit_sent', 'day_limit_sent');
        $this->renameColumn(LkClientSettings::tableName(), 'is_min_day_limit_sent', 'is_day_limit_sent');

        $this->renameColumn(LkNoticeSetting::tableName(), 'min_day_limit', 'day_limit');

        $this->delete(\app\models\LkNotificationLog::tableName(), ['event' => 'day_limit']);
        $this->update(\app\models\LkNotificationLog::tableName(), ['event' => 'day_limit'], ['event' => 'min_day_limit']);
        $this->alterColumn(\app\models\LkNotificationLog::tableName(), 'event', "enum('add_pay_notif','day_limit','zero_balance','prebil_prepayers_notif','min_balance') DEFAULT NULL");
    }
}