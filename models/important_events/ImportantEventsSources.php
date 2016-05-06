<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;

class ImportantEventsSources extends ActiveRecord
{
    const SOURCE_STAT = 'stat';

    const IMPORTANT_EVENT_SOURCE_STAT = 'stat';
    const IMPORTANT_EVENT_SOURCE_BILLING = 'billing';
    const IMPORTANT_EVENT_SOURCE_CORE = 'core';
    const IMPORTANT_EVENT_SOURCE_PLATFORM = 'platform';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'important_events_sources';
    }

    /**
     * @return []
     */
    public function rules()
    {
        return [
            [['code',], 'required'],
            [['code', 'title',], 'trim'],
            ['code', 'unique'],
        ];
    }

    /**
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'title' => 'Название',
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->title;
    }

}