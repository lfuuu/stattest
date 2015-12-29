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
            [['code',], 'required'],
            [['code', 'title',], 'trim'],
            ['code', 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'title' => 'Название',
        ];
    }

}