<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;

class ImportantEventsGroups extends ActiveRecord
{

    public static function tableName()
    {
        return 'important_events_groups';
    }

    public function rules()
    {
        return [
            [['title',], 'required'],
            [['title',], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'Название',
        ];
    }

}