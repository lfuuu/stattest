<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class ImportantEventsGroups extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'important_events_groups';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['title',], 'required'],
            [['title',], 'trim'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'title' => 'Название',
        ];
    }

    /**
     * @param bool|false $withEmpty
     * @return array
     */
    public static function getList($withEmpty = false)
    {
        $query = self::find();

        $list = ArrayHelper::map($query->asArray()->all(), 'id', 'title');
        if ($withEmpty) {
            $list = ['' => '-- Группа --'] + $list;
        }

        return $list;
    }

}