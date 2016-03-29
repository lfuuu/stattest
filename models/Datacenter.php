<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Datacenter
 *
 * @property int id
 * @property string name
 * @property string address
 * @property string comment
 * @property int region
 *
 * @package app\models
 */
class Datacenter extends ActiveRecord
{
    public static function tableName()
    {
        return 'datacenter';
    }

    public function getDatacenterRegion()
    {
        return $this->hasOne(Region::className(), ["id" => "region"]);
    }
}
