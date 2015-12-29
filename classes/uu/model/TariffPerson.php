<?php

namespace app\classes\uu\model;

/**
 * Для кого действует тариф (для всех, физиков, юриков)
 *
 * @property int $id
 * @property string $name
 */
class TariffPerson extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const ID_ALL = 1;
    const ID_NATURAL_PERSON = 2;
    const ID_LEGAL_PERSON = 3;

    public static function tableName()
    {
        return 'uu_tariff_person';
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
}