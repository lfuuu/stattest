<?php

namespace app\modules\sim\models;

use app\classes\model\ActiveRecord;
use app\models\Region;

/**
 * @property int $id
 * @property string $region_name
 * @property string $region_code
 * @property int $iccid_prefix
 * @property int $iccid_region_code
 * @property int $iccid_vendor_code
 * @property int $iccid_range_length
 * @property int $iccid_last_used
 * @property int $imsi_prefix
 * @property int $imsi_region_code
 * @property int $imsi_range_length
 * @property int $region_id
 * @property int $parent_id
 *
 * @property-read Region $region
 * @property-read RegionSettings $parent
 */
class RegionSettings extends ActiveRecord
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
        return 'sim_region_settings';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'iccid_prefix', 'iccid_region_code', 'iccid_vendor_code', 'iccid_range_length'], 'integer'],
            [['iccid_last_used'], 'integer'],
            [['imsi_prefix', 'imsi_region_code', 'imsi_range_length'], 'integer'],
            [['region_id', 'parent_id'], 'integer'],
            [['region_name', 'region_code'], 'string'],
        ];
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region_name' => 'Название региона',
            'region_code' => 'Код региона',
            'iccid_prefix' => 'ICCID префикс',
            'iccid_region_code' => 'ICCID код региона',
            'iccid_vendor_code' => 'ICCID код вендора',
            'iccid_range_length' => 'Длина диапазона ICCID',
            'iccid_last_used' => 'Последний использованный ICCID',
            'imsi_prefix' => 'IMSI префикс',
            'imsi_region_code' => 'IMSI код региона',
            'imsi_range_length' => 'Длина диапазона IMSI',
            'region_id' => 'Регион (точка подключения)',
            'parent_id' => 'Регион-родитель',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false
    ) {
        $list = self::find()
            ->with(['region', 'parent'])
            ->joinWith(['region'])
            ->where($where = [
                'AND',
                'region_id is not null',
                'imsi_region_code is not null',

                // temporary
                'regions.id is not null',
            ])
            ->orderBy($orderBy = ['regions.name' => SORT_ASC])
            ->indexBy($indexBy = 'id')
            ->all();

        $ready = [];
        /** @var self $line */
        foreach ($list as $key => $line) {
            $ready[$key] = $line->getRegionFullName();
        }

        return $ready;
    }

    /**
     * @param self $model
     * @return self
     */
    public static function getParentModel(self $model)
    {
        if ($parent = $model->parent) {
            return self::getParentModel($parent);
        }

        return $model;
    }

    /**
     * @return self
     */
    public function getMainParent()
    {
        return self::getParentModel($this);
    }

    /**
     * @return string
     */
    public function getRegionFullName()
    {
        $name = $this->region->name;
        if ($this->parent && $parent = $this->getMainParent()) {
            $name = sprintf('%s (%s)', $name, $parent->region->name);
        }

        return $name;
    }
}