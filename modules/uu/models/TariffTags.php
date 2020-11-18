<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;

/**
 * Тэги тарифа
 *
 * @property int $tariff_id
 * @property int $tag_id
 *
 * @property-read Tariff $tariff
 * @property-read Tag $tag
 *
 * @method static TariffTags findOne($condition)
 * @method static TariffTags[] findAll($condition)
 */
class TariffTags extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_tags';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'tag_id'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
            ]
        );
    }

    public function attributeLabels()
    {
        return [
            'id' => '№',
            'tariff_id' => 'Тариф',
            'tag_id' => 'Тэг',
        ];
    }

    public function getParentId()
    {
        return $this->tariff_id;
    }

    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }

    public function getTag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }

    public function __toString()
    {
        return $this->tag->name;
    }
}