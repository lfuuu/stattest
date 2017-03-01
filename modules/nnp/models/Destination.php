<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Направление
 * Группировка префиксов
 *
 * @property int id
 * @property string name
 *
 * @property PrefixDestination[] prefixDestinations
 * @property PrefixDestination[] additionPrefixDestinations
 * @property PrefixDestination[] subtractionPrefixDestinations
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
        ];
    }

    /**
     * Returns the database connection
     *
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
        return $this->hasMany(PrefixDestination::className(), ['destination_id' => 'id'])
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
}