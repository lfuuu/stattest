<?php

use app\models\important_events\ImportantEventsGroups;
use app\models\important_events\ImportantEventsNames;

/**
 * Class m190902_120000_add_sbis_important_events
 */
class m190902_120000_add_sbis_important_events extends \app\classes\Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // insert important event group
        $this->insert(ImportantEventsGroups::tableName(), [
            'id' => ImportantEventsGroups::ID_DOCUMENT_FLOW,
            'title' => 'Документооборот',
        ]);

        // insert important events
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::SBIS_DOCUMENT_SENT,
            'value' => 'Пакет документов отправлен в СБИС',
            'group_id' => ImportantEventsGroups::ID_DOCUMENT_FLOW,
        ]);
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::SBIS_DOCUMENT_ACCEPTED,
            'value' => 'Пакет документов в СБИС утверждён',
            'group_id' => ImportantEventsGroups::ID_DOCUMENT_FLOW,
        ]);
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::SBIS_DOCUMENT_EVENT,
            'value' => 'Статус отправленного пакета документов в СБИС изменён',
            'group_id' => ImportantEventsGroups::ID_DOCUMENT_FLOW,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (!ImportantEventsGroups::findOne(['id' => ImportantEventsGroups::ID_DOCUMENT_FLOW])) {
            return;
        }

        $this->delete(ImportantEventsNames::tableName(), [
            'group_id' => [ImportantEventsGroups::ID_DOCUMENT_FLOW]
        ]);

//        $this->delete(ImportantEventsGroups::tableName(), [
//            'id' => [ImportantEventsGroups::ID_DOCUMENT_FLOW]
//        ]);
    }
}
