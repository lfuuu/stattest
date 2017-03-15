<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class SaleChannelOld extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'sale_channels_old';
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['dealer_id', 'is_agent', 'courier_id'], 'integer'],
            ['interest', 'double'],
            [['dealer_id', 'is_agent', 'interest', 'courier_id'], 'default', 'value' => 0]
        ];
    }

    /**
     * @return string
     */
    public function getCourierName()
    {
        if (!$this->courier_id) {
            return '';
        }

        $courier = Courier::findOne($this->courier_id);
        return ($courier) ? $courier->name : '';
    }
}
