<?php

namespace app\classes\uu\model;

/**
 * Типы VM
 * @link http://datacenter.mcn.ru/vps-hosting/
 *
 * @property int $id
 * @property string $name
 */
class TariffVm extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    public static function tableName()
    {
        return 'uu_tariff_vm';
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