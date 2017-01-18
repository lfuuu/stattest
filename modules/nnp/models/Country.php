<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int code
 * @property string name
 * @property string name_rus
 * @property int prefix
 */
class Country extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const RUSSIA = 643;
    const HUNGARY = 348;
    const GERMANY = 276;
    const SLOVAKIA = 703;
    const AUSTRIA = 40;
    const CZECH = 203;

    public static $primaryField = 'code';

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'name' => 'Эндоним',
            'name_rus' => 'Русское название',
            'prefix' => 'Префикс',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.country';
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
        return $this->name_rus;
    }

    /**
     * По какому полю сортировать для getList()
     *
     * @return array
     */
    public static function getListOrderBy()
    {
        return ['name_rus' => SORT_ASC];
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/country/', 'CountryFilter[code]' => $id]);
    }
}
