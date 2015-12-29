<?php

namespace app\classes\uu\model;

/**
 * Типы тарификации тарифов телефонии (посекундный, поминутный и пр.)
 *
 * @property int $id
 * @property string $name
 */
class TariffVoipTarificate extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const ID_VOIP_BY_SECOND = 1;
    const ID_VOIP_BY_SECOND_FREE = 2;
    const ID_VOIP_BY_MINUTE = 3;
    const ID_VOIP_BY_MINUTE_FREE = 4;

    public static function tableName()
    {
        return 'uu_tariff_voip_tarificate';
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