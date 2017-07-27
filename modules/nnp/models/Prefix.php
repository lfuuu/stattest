<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Префикс
 * Группировка диапазонов номеров
 *
 * @property int $id
 * @property string $name
 *
 * @property NumberRangePrefix[] $numberRangePrefixes
 *
 * @property PrefixDestination[] $prefixDestinations
 * @property PrefixDestination[] $additionPrefixDestinations
 * @property PrefixDestination[] $subtractionPrefixDestinations
 */
class Prefix extends ActiveRecord
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
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.prefix';
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
        return Url::to(['/nnp/prefix/edit', 'id' => $id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumberRangePrefixes()
    {
        return $this->hasMany(NumberRangePrefix::className(), ['prefix_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrefixDestinations()
    {
        return $this->hasMany(PrefixDestination::className(), ['prefix_id' => 'id']);
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