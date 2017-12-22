<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Class Datacenter
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $comment
 * @property int $region
 */
class Datacenter extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'datacenter';
    }

    /**
     * @return ActiveQuery
     */
    public function getDatacenterRegion()
    {
        return $this->hasOne(Region::className(), ["id" => "region"]);
    }
}
