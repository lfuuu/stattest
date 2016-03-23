<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;
use \app\classes\traits\GetListTrait;

/**
 * @property string $title
 * @package app\models\important_events
 */
class ImportantEventsGroups extends ActiveRecord
{

    use GetListTrait;

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
     * По какому полю сортировать для getList()
     * @return []
     */
    public static function getListOrderBy()
    {
        return ['id' => SORT_ASC];
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

}