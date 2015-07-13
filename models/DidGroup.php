<?php
namespace app\models;

use app\dao\DidGroupDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 *
 * @property City $city
 * @property
 */
class DidGroup extends ActiveRecord
{
    public static function tableName()
    {
        return 'did_group';
    }

    public static function dao()
    {
        return DidGroupDao::me();
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }
}