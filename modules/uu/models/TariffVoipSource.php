<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\Country;
use app\models\voip\Source;
use yii\db\ActiveQuery;

/**
 * Телефония. Источники
 *
 * @property integer $id
 * @property integer $tariff_id
 * @property integer $source_code
 *
 * @property-read Tariff $tariff
 * @property-read Source $source
 *
 * @method static TariffVoipSource findOne($condition)
 * @method static TariffVoipSource[] findAll($condition)
 */
class TariffVoipSource extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

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

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_voip_source';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'source_code',], 'required'],
            [['tariff_id',], 'integer'],
            [['source_code',], 'string'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Source::class, ['code' => 'source_code']);
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->source->name;
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return [
            'id',
            'tariff_id',
        ];
    }

    /**
     * Вернуть ID родителя
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->tariff_id;
    }

    /**
     * Установить ID родителя
     *
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->tariff_id = $parentId;
    }
}
