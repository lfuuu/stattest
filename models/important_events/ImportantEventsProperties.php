<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;

class ImportantEventsProperties extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'important_events_properties';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

}