<?php

use app\classes\Migration;
use app\models\important_events\ImportantEventsGroups;
use app\models\important_events\ImportantEventsNames;

/**
 * Class m200720_131141_event_doc_uploaded
 */
class m200720_131141_event_doc_uploaded extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::DOCUMENT_UPLOADED_LK,
            'value' => 'Документ загружен (из ЛК)',
            'group_id' => ImportantEventsGroups::ID_DOCUMENT_FLOW,
            'comment' => 'Документ загружен (из ЛК)',
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::DOCUMENT_UPLOADED_LK,
        ]);
    }
}
