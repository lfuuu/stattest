<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetListTrait;

/**
 * Тэги тарифа
 *
 * @property int $id
 * @property int $name
 *
 * @property-read getTariffTags[] $tariffTags
 */
class Tag extends ActiveRecord
{
    use GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_tag';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['name', 'string'],
            ['name', 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '№',
            'name' => 'Название',
            'tags' => 'Тэги',
        ];
    }

    public function getTariffTags()
    {
        return $this->hasMany(TariffTags::class, ['tag_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }
}