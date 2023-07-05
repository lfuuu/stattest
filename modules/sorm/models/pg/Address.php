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
        return \Yii::$app->dbPg;
    }

}
