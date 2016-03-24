<?php
namespace app\models\message;

use app\models\important_events\ImportantEventsNames;
use yii\db\ActiveRecord;

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
        return $this->hasOne(ImportantEventsNames::className(), ['code' => 'event_code']);
    }

}