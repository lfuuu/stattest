<?php

use app\models\important_events\ImportantEventsGroups;
use app\models\important_events\ImportantEventsNames;

/**
 * Class m191105_122506_add_sbis_important_events
 */
class m191105_122506_add_sbis_important_events extends \app\classes\Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::SBIS_DRAFT_CREATED,
            'value' => 'Создан черновик пакета документов в СБИС',
            'group_id' => ImportantEventsGroups::ID_DOCUMENT_FLOW,
        ]);
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::SBIS_DOCUMENT_CREATED,
            'value' => 'Создан пакет документов в СБИС',
            'group_id' => ImportantEventsGroups::ID_DOCUMENT_FLOW,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(ImportantEventsNames::tableName(), [
            'code' => [ImportantEventsNames::SBIS_DOCUMENT_CREATED]
        ]);

        $this->delete(ImportantEventsNames::tableName(), [
            'code' => [ImportantEventsNames::SBIS_DRAFT_CREATED]
        ]);
    }
}
