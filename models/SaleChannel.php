<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class SaleChannel extends ActiveRecord
{
    public static function tableName()
    {
        return 'sale_channels';
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'dealer_id' => 'ID дилера',
            'is_agent' => 'Агент',
            'interest' => 'Вознаграждение',
            'courierName' => 'Курьер',
            'courier_id' => 'Курьер',
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['dealer_id', 'is_agent', 'courier_id'], 'integer'],
            ['interest', 'double'],
            [['dealer_id', 'is_agent' ,'interest', 'courier_id'], 'default', 'value' => 0]
        ];
    }

    public static function getList()
    {
        $arr = self::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getCourierName()
    {
        if(!$this->courier_id)
            return '';
        $courier = Courier::findOne($this->courier_id);
        return ($courier) ? $courier->name : '';
    }
}