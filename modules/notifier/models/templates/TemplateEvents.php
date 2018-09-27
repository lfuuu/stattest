<?php

namespace app\modules\notifier\models\templates;

use app\classes\model\ActiveRecord;
use app\models\important_events\ImportantEventsNames;

/**
 * @property int $template_id
 * @property string $event_code
 */
class TemplateEvents extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'message_templates_events';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(ImportantEventsNames::class, ['code' => 'event_code']);
    }

}
