<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $code
 * @property string $name
 * @property string $name_rus
 * @property int $prefix
 *
 * @link https://ru.wikipedia.org/wiki/%D0%9E%D0%B1%D1%89%D0%B5%D1%80%D0%BE%D1%81%D1%81%D0%B8%D0%B9%D1%81%D0%BA%D0%B8%D0%B9_%D0%BA%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%BE%D1%80_%D1%81%D1%82%D1%80%D0%B0%D0%BD_%D0%BC%D0%B8%D1%80%D0%B0
 */
class Country extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const RUSSIA_CODE = 643;
    const RUSSIA_PREFIX = 7;

    const HUNGARY_CODE = 348;
    const HUNGARY_PREFIX = 36;

    const GERMANY_CODE = 276;
    const GERMANY_PREFIX = 49;

    const SLOVAKIA_CODE = 703;
    const SLOVAKIA_PREFIX = 421;

    const AUSTRIA_CODE = 40;
    const AUSTRIA_PREFIX = 43;

    const CZECH_CODE = 203;
    const CZECH_PREFIX = 420;

    const ROMANIA_CODE = 642;
    const ROMANIA_PREFIX = 40;

    const CROATIA_CODE = 191;
    const CROATIA_PREFIX = 385;

    const SERBIA_CODE = 688;
    const SERBIA_PREFIX = 381;

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
            'name' => 'Английское название',
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
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
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
            $indexBy = 'code',
            $select = 'name_rus',
            $orderBy = ['name_rus' => SORT_ASC],
            $where = []
        );
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
