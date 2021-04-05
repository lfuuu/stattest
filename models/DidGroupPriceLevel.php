<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class DidGroupPriceLevel
 * @property int $id
 * @property int $did_group_id
 * @property int $pricelevel_id
 * @property int $price
 * @property int $tariff_status_main_id
 * @property int $tariff_status_package_id
 * 
 *
 */
class DidGroupPriceLevel extends ActiveRecord
{

    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    public static $primaryField = 'id';

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'did_group_id' => 'DID группа',
            'price_level_id' => 'ID уровня цен',
            'price' => 'цена',
            'tariff_status_main_id' => 'статус мэйн',
            'tariff_status_package_id' => 'статус  пакэдж',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['did_group_id'], 'integer'],
            [['price_level_id'], 'integer'],
            [['price'], 'integer'],
            [['tariff_status_main_id'], 'integer'],
            [['tariff_status_package_id'], 'integer'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'did_group_price_level';
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['id' => SORT_ASC],
            $where = []
        );
    }
}
