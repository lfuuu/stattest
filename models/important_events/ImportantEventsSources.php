<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;

class ImportantEventsSources extends ActiveRecord
{

    public static function tableName()
    {
        return 'important_events_sources';
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