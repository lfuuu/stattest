<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;

/**
 * Тэг тарифа
 *
 * @property int $id
 * @property string $name
 */
class TariffTag extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ID_HIT = 1;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_tag';
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
}