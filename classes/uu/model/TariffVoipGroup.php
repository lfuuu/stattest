<?php

namespace app\classes\uu\model;

/**
 * Группа тарифов телефонии
 *
 * @property int $id
 * @property string $name
 */
class TariffVoipGroup extends \yii\db\ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const ID_DEFAULT = 1;

    public static function tableName()
    {
        return 'uu_tariff_voip_group';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
        ];
    }

    /**
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
     * @return []
     */
    public static function getListOrderBy()
    {
        return ['id' => SORT_ASC];
    }
}