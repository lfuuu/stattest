<?php
namespace app\modules\nnp\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $code
 * @property string $name
 * @property string $name_rus
 * @property string $alpha_3
 * @property string $prefixes integer[]
 *
 * @link https://ru.wikipedia.org/wiki/%D0%9E%D0%B1%D1%89%D0%B5%D1%80%D0%BE%D1%81%D1%81%D0%B8%D0%B9%D1%81%D0%BA%D0%B8%D0%B9_%D0%BA%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%BE%D1%80_%D1%81%D1%82%D1%80%D0%B0%D0%BD_%D0%BC%D0%B8%D1%80%D0%B0
 */
class Country extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const RUSSIA_PREFIX = 7;

    // Европа
    const HUNGARY_CODE = 348;
    const GERMANY_CODE = 276;
    const SLOVAKIA_CODE = 703;
    const AUSTRIA_CODE = 40;
    const CZECH_CODE = 203;
    const POLAND_CODE = 616;
    const ROMANIA_CODE = 642;
    const BULGARIA_CODE = 100;
    const CROATIA_CODE = 191;
    const SERBIA_CODE = 688;
    const BELGIUM_CODE = 56;
    const UNITED_KINGDOM_CODE = 826;
    const IRELAND_CODE = 372;
    const LIECHTENSTEIN_CODE = 438;
    const LUXEMBOURG_CODE = 442;
    const MONACO_CODE = 492;
    const NETHERLANDS_CODE = 528;
    const FRANCE_CODE = 250;
    const SWITZERLAND_CODE = 756;
    const DENMARK_CODE = 208;
    const ICELAND_CODE = 352;
    const NORWAY_CODE = 578;
    const LATVIA_CODE = 428;
    const LITHUANIA_CODE = 440;
    const FINLAND_CODE = 246;
    const SWEDEN_CODE = 752;
    const ESTONIA_CODE = 233;
    const ALBANIA_CODE = 8;
    const ANDORRA_CODE = 20;
    const BOSNIA_CODE = 70;
    const VATICAN_CODE = 336;
    const GREECE_CODE = 300;
    const SPAIN_CODE = 724;
    const ITALY_CODE = 380;
    const MACEDONIA_CODE = 807;
    const MALTA_CODE = 470;
    const PORTUGAL_CODE = 620;
    const SAN_MARINO_CODE = 674;
    const SLOVENIA_CODE = 705;
    const MONTENEGRO_CODE = 499;
    const CYPRUS_CODE = 196;

    // СНГ
    const RUSSIA_CODE = 643;
    const AZERBAIJAN_CODE = 31;
    const ARMENIA_CODE = 51;
    const GEORGIA_CODE = 268;
    const BELARUS_CODE = 112;
    const KAZAKHSTAN_CODE = 398;
    const KYRGYZSTAN_CODE = 417;
    const MOLDOVA_CODE = 498;
    const MONGOLIA_CODE = 496;
    const TAJIKISTAN_CODE = 762;
    const TURKMENISTAN_CODE = 795;
    const UZBEKISTAN_CODE = 860;
    const UKRAINE_CODE = 804;

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
            'prefixes' => 'Префиксы',
            'alpha_3' => '3х-буквенный код',
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
