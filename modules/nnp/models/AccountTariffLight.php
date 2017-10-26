<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * Универсальные услуги для низкоуровневого биллинга
 * Копия \app\modules\uu\models\AccountTariff
 *
 * @property int $id
 * @property int $account_client_id
 * @property int $tariff_id
 * @property int $activate_from
 * @property int $deactivate_from
 * @property float $coefficient
 * @property int $account_tariff_id
 * @property float $price
 * @property int $service_type_id
 */
class AccountTariffLight extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const COEFFICIENT_PRECISION = 8;

    protected $isAttributeTypecastBehavior = true;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            // ID ежемесячного списания за пакет (AccountLogPeriod.id)
            'account_client_id' => 'ЛС',
            // ID ЛС (AccountClient.id)
            'tariff_id' => 'Тариф',
            // ID пакета (Tariff.id, он же nnp.Package.tariff_id)
            'activate_from' => 'С',
            // Дата начала действия пакета по UTC
            'deactivate_from' => 'По',
            // Дата окончания действия пакета по UTC
            'coefficient' => 'Коэффициент',
            // Коэффициент от 0 до 1, если пакет действует меньше месяца. Цену и кол-во доступных минут надо умножать на этот коэффициент
            'account_tariff_id' => 'Базовая услуга',
            // ID базовой (не пакет!) универсальной услуги (AccountTariff.id). Для пакетов телефонии (service_type_id = 3) это номер телефона/линии (он же billing.service_number.id). Для пакетов транков (service_type_id = 23, 24) это номер транка (он же billing.service_trunk.id). Чтобы не пересекаться со старыми услугами - больше 100000.
            'price' => 'Цена пакета',
            // Цена пакета. Для получения стоимости надо умножить на coefficient
            'service_type_id' => 'Тип услуги',
            // ID типа услуги (ServiceType.id). 3 - пакет телефонии. 23 - пакет терм-транк. 24 - пакет ориг-транк
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.account_tariff_light';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }
}