<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveRecord;

/**
 * Универсальные услуги для низкоуровневого биллинга
 * Копия \app\classes\uu\model\AccountTariff
 *
 * @property int id
 * @property int account_client_id
 * @property int tariff_id
 * @property int activate_from
 * @property int deactivate_from
 * @property float coefficient
 * @property int account_tariff_id
 * @property bool tariffication_by_minutes
 * @property bool tariffication_full_first_minute
 * @property bool tariffication_free_first_seconds
 * @property float price
 * @property int service_type_id
 */
class AccountTariffLight extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID', // фактически это AccountLogPeriod.id
            'account_client_id' => 'Аккаунт клиента',
            'tariff_id' => 'Тариф', // не путайте с tariff_period_id
            'activate_from' => 'С',
            'deactivate_from' => 'По',
            'coefficient' => 'Коэффициент', // если подключение не в начале месяца
            'account_tariff_id' => 'Базовая услуга', // тариф (2), а не пакет (3)!
            'tariffication_by_minutes' => 'Поминутно?',
            'tariffication_full_first_minute' => 'Первая минута поминутно?',
            'tariffication_free_first_seconds' => 'Первые 5 сек. бесплатно?',
            'price' => 'Цена пакета', // полная (из TariffPeriod), без учета coefficient
            'service_type_id' => 'Тип услуги',
        ];
    }

    /**
     * имя таблицы
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.account_tariff_light';
    }

    /**
     * Returns the database connection
     * @return Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }
}