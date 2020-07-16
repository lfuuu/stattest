<?php

namespace app\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $code
 * @property string $timezone_name
 * @property int $country_id
 * @property int $type_id
 * @property int $is_active
 * @property int $is_use_sip_trunk
 *
 * @property-read Datacenter $datacenter
 * @property-read Country $country
 * @property-read City[] $cities
 */
class Timezone extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }


    /**
     * @return string
     */
    public static function tableName()
    {
        return 'timzone';
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'code' => 'ID',
            'name' => 'Название',
            'order' => 'Порядок',

        ];
    }

    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'code',
            $select = 'name',
            $orderBy = ['order' => SORT_ASC],
            $where = []
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}