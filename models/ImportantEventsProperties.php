<?php

namespace app\models;

use yii\db\ActiveRecord;

class ImportantEventsProperties extends ActiveRecord
{

    public static function tableName()
    {
        return 'important_events_properties';
    }

}