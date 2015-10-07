<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class NewSaleChannel extends ActiveRecord
{
    public static function tableName()
    {
        return 'sale_channel';
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }

    public static function getList()
    {
        return ArrayHelper::map(static::find()->all(), 'id', 'name');
    }
}