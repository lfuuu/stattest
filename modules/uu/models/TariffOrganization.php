<?php

namespace app\modules\uu\models;

use app\classes\model\HistoryActiveRecord;
use app\models\Organization;
use yii\db\ActiveQuery;

/**
 * Организации тарифа
 *
 * @property integer $id
 * @property integer $tariff_id
 * @property integer $organization_id
 *
 * @property-read Tariff $tariff
 * @property-read Organization $organization
 */
class TariffOrganization extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return parent::behaviors() + [
                'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_organization';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'organization_id',], 'integer'],
            [['tariff_id', 'organization_id',], 'required'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['organization_id' => 'organization_id']);
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->organization->name;
    }

}
