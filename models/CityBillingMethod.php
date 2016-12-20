<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 */
class CityBillingMethod extends ActiveRecord
{

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'city_billing_methods';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', ], 'integer'],
            [['name',], 'string'],
            [['name',], 'required'],
        ];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/dictionary/city-billing-methods/edit', 'id' => $id]);
    }

}