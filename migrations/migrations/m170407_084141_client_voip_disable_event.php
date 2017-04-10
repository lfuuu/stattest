<?php
use app\models\important_events\ImportantEventsGroups;
use app\models\important_events\ImportantEventsNames;

/**
 * Class m170407_084141_client_voip_disable_event
 */
class m170407_084141_client_voip_disable_event extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::IMPORTANT_EVENT_SET_LOCAL_BLOCK,
            'value' => 'Установлена локальная блокировка',
            'group_id' => ImportantEventsGroups::ID_ACCOUNT
        ]);

        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::IMPORTANT_EVENT_UNSET_LOCAL_BLOCK,
            'value' => 'Снята локальная блокировка',
            'group_id' => ImportantEventsGroups::ID_ACCOUNT
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ImportantEventsNames::tableName(), ['code' => [
            ImportantEventsNames::IMPORTANT_EVENT_SET_LOCAL_BLOCK,
            ImportantEventsNames::IMPORTANT_EVENT_UNSET_LOCAL_BLOCK
        ]]);
    }
}
