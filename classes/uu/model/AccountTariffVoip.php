<?php

namespace app\classes\uu\model;

use app\classes\Html;
use app\classes\uu\forms\AccountLogFromToTariff;
use app\models\ClientAccount;
use app\models\Region;
use DateTime;
use DateTimeImmutable;
use RangeException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Универсальная услуга для телефонии
 */
class AccountTariffVoip extends AccountTariff
{
    public $voip_country_id = null;
    public $voip_number_type = null;
    public $voip_regions = [];
    public $voip_did_group = null;
    public $voip_numbers_list_class = 2;
    public $voip_numbers_list_order_by_field = 'number';
    public $voip_numbers_list_order_by_type = SORT_ASC;
    public $voip_numbers_list_mask = '';

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
            'voip_country_id' => 'Страна',
            'voip_number_type' => 'Тип',
            'voip_regions' => 'Город',
            'voip_did_group' => 'DID группа',
            'voip_numbers_list_class' => 'Количество столбцов',
            'voip_numbers_list_order_by_field' => 'Сортировка по полю',
            'voip_numbers_list_order_by_type' => 'Тип сортировки',
            'voip_numbers_list_mask' => 'Шаблон поиска',
        ];
    }
}