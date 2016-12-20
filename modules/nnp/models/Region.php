<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int id
 * @property string name
 * @property int country_prefix
 */
class Region extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'country_prefix' => 'Страна', // префикс
        ];
    }

    /**
     * имя таблицы
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.region';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['country_prefix'], 'integer'],
            [['name', 'country_prefix'], 'required'],
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
        return Url::to(['/nnp/region/edit', 'id' => $id]);
    }

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @param int $countryPrefix
     * @return self[]
     */
    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false, $countryPrefix = null)
    {
        $activeQuery = self::find();
        $countryPrefix && $activeQuery->andWhere(['country_prefix' => $countryPrefix]);
        $list = $activeQuery
            ->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }
}
