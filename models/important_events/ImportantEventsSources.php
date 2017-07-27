<?php

namespace app\models\important_events;

use app\classes\model\ActiveRecord;

class ImportantEventsSources extends ActiveRecord
{
    const SOURCE_STAT = 'stat';
    const SOURCE_LK = 'lk';
    const SOURCE_BILLING = 'billing';
    const SOURCE_CORE = 'core';
    const SOURCE_PLATFORM = 'platform';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'important_events_sources';
    }

    /**
     * @return array
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
     * @return array
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