<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
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
 *
 * @method static TariffOrganization findOne($condition)
 * @method static TariffOrganization[] findAll($condition)
 */
class TariffOrganization extends ActiveRecord
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
                \app\classes\behaviors\HistoryChanges::className(),
            ]
        );
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

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'organization_id':
                /** @var Organization $actual */
                $actual = Organization::find()->byId($value)->actual()->one();
                if ($actual instanceof Organization) {
                    return $actual->getLink();
                }
                break;
        }

        return $value;
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