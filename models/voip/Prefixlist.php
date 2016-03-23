<?php
namespace app\models\voip;

use yii\db\ActiveRecord;

/**
 * Class Prefixlist
 *
 * @property int id
 * @property string name
 * @property int type_id
 * @property string sub_type
 * @property string prefixes
 * @property int country_id
 * @property int region_id
 * @property int city_id
 * @property int exclude_operators
 * @property string operators
 *
 * @package app\models\voip
 */
class Prefixlist extends ActiveRecord
{

    const TYPE_MANUAL = 1;
    const TYPE_ROSLINK = 3;

    public static $types = [
        self::TYPE_MANUAL => 'Вручную',
        self::TYPE_ROSLINK => 'РосСвязь',
    ];

    const TYPE_ROSLINK_ALL = 'all';
    const TYPE_ROSLINK_FIXED = 'fixed';
    const TYPE_ROSLINK_MOBILE = 'mobile';

    public static $roslink_types = [
        self::TYPE_ROSLINK_ALL => 'Любые',
        self::TYPE_ROSLINK_FIXED => 'Стационарные',
        self::TYPE_ROSLINK_MOBILE => 'Мобильные',
    ];

    public static function tableName()
    {
        return 'voip_prefixlist';
    }

    public function afterFind()
    {
        $this->prefixes = explode(',', $this->prefixes);
        $this->operators = explode(',', $this->operators);

        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        $this->prefixes = implode(',', $this->prefixes);
        $this->operators = implode(',', $this->operators);

        return parent::beforeSave($insert);
    }

}