<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Направление
 * Группировка префиксов
 *
 * @property int $id
 * @property int $land_id
 * @property int $status_id
 * @property string $name
 * @property int country_id
 *
 * @property-read Land $land
 * @property-read Status $status
 * @property-read PrefixDestination[] $prefixDestinations
 * @property-read PrefixDestination[] $additionPrefixDestinations
 * @property-read PrefixDestination[] $subtractionPrefixDestinations
 */
class Destination extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'land_id' => 'Группа',
            'status_id' => 'Статус',
            'country_id' => 'Страна',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.destination';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['land_id', 'status_id', 'country_id'], 'integer'],
        ];
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

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/destination/edit', 'id' => $id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrefixDestinations()
    {
        return $this->hasMany(PrefixDestination::class, ['destination_id' => 'id'])
            ->indexBy('prefix_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getAdditionPrefixDestinations()
    {
        return $this->getPrefixDestinations()
            ->where([PrefixDestination::tableName() . '.is_addition' => true]);
    }

    /**
     * @return ActiveQuery
     */
    public function getSubtractionPrefixDestinations()
    {
        return $this->getPrefixDestinations()
            ->where([PrefixDestination::tableName() . '.is_addition' => false]);
    }

    /**
     * @return ActiveQuery
     */
    public function getLand()
    {
        return $this->hasOne(Land::class, ['id' => 'land_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Land::class, ['id' => 'status_id']);
    }


    /**
     * @return bool
     */
    public function isMobile()
    {
        $name = mb_strtolower($this->name);
        return strpos($name, 'mob') !== false || strpos($name, 'моб') !== false;
    }

    /**
     * @return bool
     */
    public function isLocal()
    {
        $name = mb_strtolower($this->name);
        return strpos($name, 'fix') !== false || strpos($name, 'стац') !== false;
    }
}