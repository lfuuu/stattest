<?php

namespace app\classes\uu\model;

use yii\db\ActiveQuery;

/**
 * Статусы тарифа (публичный, специальный, архивный и пр.)
 *
 * @property int $id
 * @property string $name
 * @property int $service_type_id Тип услуги. Если null, то для всех
 *
 * @property ServiceType $serviceType
 */
class TariffStatus extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListServiceTypeTrait;

    const ID_PUBLIC = 1;
    const ID_SPECIAL = 2;
    const ID_ARCHIVE = 3;
    const ID_TEST = 4;

    const ID_VOIP_8800 = 5;
    const ID_VOIP_OPERATOR = 6;
    const ID_VOIP_TRANSIT = 7;

    const ID_INTERNET_ADSL = 8;

    const ID_VOIP_8800_TEST = 9;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_status';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'service_type_id'], 'integer'],
            [['name'], 'string'],
            [['name', 'service_type_id'], 'required'],
        ];
    }

    /**
     * По какому полю сортировать для getList()
     *
     * @return array
     */
    public static function getListOrderBy()
    {
        return ['id' => SORT_ASC];
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::className(), ['id' => 'service_type_id']);
    }
}