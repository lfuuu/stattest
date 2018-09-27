<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;
use yii\helpers\Url;

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
        return $this->hasOne(Region::class, ["id" => "region"]);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/', 'module' => 'routers', 'action' => 'datacenter_apply', 'id' => $id]);
    }
}
