<?php

namespace app\modules\sorm\models\pg;

use app\classes\model\ActiveRecord;

/**
 * Class Address
 *
 * @property string $hash
 * @property string $address
 * @property string $state
 * @property string $post_code
 * @property string $country
 * @property string $district_type
 * @property string $district
 * @property string $region_type
 * @property string $region
 * @property string $city_type
 * @property string $city
 * @property string $street_type
 * @property string $street
 * @property string $house
 * @property string $housing
 * @property string $flat_type
 * @property string $flat
 * @property string $address_nostruct
 * @property string $json
 * @property string $unparsed_parts
 * @property string $is_struct
 * @property string $use_address
 */
class Address extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'sorm_itgrad.address';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
//        return \Yii::$app->dbPg;
        return \Yii::$app->dbPgLeg;
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public function attributeLabels()
    {
        return [
            'address' => 'Адрес оригинальный',
            'state' => 'Состояние',
            'post_code' => 'Почтовый индекс',
            'country' => 'Страна',
            'district_type' => 'district_type',
            'district' => 'district',
            'region_type' => 'Тип района',
            'region' => 'Район',
            'city_type' => 'Тип города',
            'city' => 'Город',
            'street_type' => 'Тип улицы',
            'street' => 'улица',
            'house' => 'Номер дома',
            'housing' => 'Корпус',
            'flat_type' => 'Тип помещения',
            'flat' => 'номер помещения',
            'address_nostruct' => 'не структурированный адрес',
            'json' => 'JSON',
            'unparsed_parts' => 'Не распознанные части адреса',
        ];
    }


    public function rules()
    {
        $modelFields = [
            'state',
            'post_code',
            'country',
            'district_type',
            'district',
            'region_type',
            'region',
            'city_type',
            'city',
            'street_type',
            'street',
            'house',
            'housing',
            'flat_type',
            'flat',
            'address_nostruct',
        ];

        $rules = [[$modelFields, 'safe', 'skipOnEmpty' => true]];

        return $rules;
    }

    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    public static function getStateList()
    {
        return [
            'need_check' => 'Необходима проверка',
            'added' => 'Ожидается распознование',
            'checked' => 'Всё ОК',
        ];
    }

}
