<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use app\classes\uu\model\Tariff;
use app\models\billing\Pricelist;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Пакеты
 *
 * @property int id
 * @property string name
 * @property int tariff_id
 * @property int package_type_id
 * @property int period_id
 * @property float price
 * @property int minute
 * @property int pricelist_id
 * @property int destination_id
 *
 * @property Tariff tariff  FK нет, ибо в таблица в другой БД
 * @property Destination destination
 * @property Pricelist pricelist  FK нет, ибо в таблица в другой БД
 */
class Package extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const PACKAGE_TYPE_MINUTE = 1; // направление + минуты
    const PACKAGE_TYPE_PRICE = 2; // направление + цена
    const PACKAGE_TYPE_PRICELIST = 3; // прайслист

    const PERIOD_SINGLE = 1; // разовый
    const PERIOD_PERIOD = 2; // периодический

    /**
     * имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'tariff_id' => 'Тариф',
            'package_type_id' => 'Тип пакета',
            'period_id' => 'Периодичность',
            'price' => 'Цена',
            'minute' => 'Кол-во минут',
            'pricelist_id' => 'Прайслист',
            'destination_id' => 'Направление',
        ];
    }

    /**
     * имя таблицы
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.package';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }

    /**
     * Returns the database connection
     * @return Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDestination()
    {
        return $this->hasOne(Destination::className(), ['id' => 'destination_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPricelist()
    {
        return $this->hasOne(Pricelist::className(), ['id' => 'pricelist_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/package/edit', 'id' => $id]);
    }
}