<?php

use app\models\important_events\ImportantEventsGroups;
use app\models\important_events\ImportantEventsNames;

/**
 * Class m210215_141516_add_porting_important_events
 */
class m210215_141516_add_porting_important_events extends \app\classes\Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::PORTING_FROM_MCN,
            'value' => 'Номер портирован от нас',
            'group_id' => ImportantEventsGroups::ID_ACCOUNT,
        ]);
        $this->insert(ImportantEventsNames::tableName(), [
            'code' => ImportantEventsNames::PORTING_TO_MCN,
            'value' => 'Номер портирован к нам',
            'group_id' => ImportantEventsGroups::ID_ACCOUNT,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(ImportantEventsNames::tableName(), [
            'code' => [ImportantEventsNames::PORTING_TO_MCN]
        ]);

        $this->delete(ImportantEventsNames::tableName(), [
            'code' => [ImportantEventsNames::PORTING_FROM_MCN]
        ]);
    }
}
