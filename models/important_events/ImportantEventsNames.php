<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;

class ImportantEventsNames extends ActiveRecord
{

    public static function tableName()
    {
        return 'important_events_names';
    }

}