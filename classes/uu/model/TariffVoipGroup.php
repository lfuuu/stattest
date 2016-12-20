<?php

namespace app\classes\uu\model;

/**
 * Группа тарифов телефонии (местные, междугородние, международные и пр.)
 *
 * @property int $id
 * @property string $name
 */
class TariffVoipGroup extends \yii\db\ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const ID_DEFAULT = 1;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_voip_group';
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
            'name' => 'Название',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['name'], 'required'],
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
}