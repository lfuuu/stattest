<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $used_times
 */
class Tags extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name',], 'string'],
            [['name',], 'required'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'used_times' => 'Кол-вол использований',
        ];
    }

    /**
     * @return array
     */
    public function getResourceNames()
    {
        return $this
            ->hasMany(TagsResource::className(), ['tag_id' => 'id'])
            ->select('resource')
            ->groupBy('resource')
            ->column();
    }

}