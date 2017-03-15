<?php
namespace app\models;

use app\classes\traits\GridSortTrait;
use app\dao\CountryDao;
use app\models\dictionary\PublicSiteCountry;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $code
 * @property string $alpha_3
 * @property string $name
 * @property string $name_rus
 * @property string $name_rus_full
 * @property int $in_use
 * @property string $lang
 * @property string $currency_id
 * @property integer $prefix
 * @property integer $order
 *
 * @property Currency $currency
 * @property PublicSiteCountry[] $publicSiteCountries
 */
class Country extends ActiveRecord
{
    use GridSortTrait;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const RUSSIA = 643;
    const HUNGARY = 348;
    const GERMANY = 276;
    const SLOVAKIA = 703;
    const AUSTRIA = 40;
    const CZECH = 203;

    public static $primaryField = 'code';

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'code' => 'ID',
            'alpha_3' => 'Сокращение',
            'name' => 'Эндоним',
            'name_rus' => 'Русское название',
            'name_rus_full' => 'Полное русское название',
            'in_use' => 'Включен',
            'lang' => 'Язык',
            'currency_id' => 'Валюта',
            'prefix' => 'Префикс',
            'site' => 'URL сайта',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['alpha_3', 'name', 'currency_id', 'lang', 'site', 'name_rus', 'name_rus_full'], 'string'],
            [['code', 'in_use', 'prefix'], 'integer'],
            [['code', 'name', 'in_use', 'lang'], 'required'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'country';
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['code'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublicSiteCountries()
    {
        return $this->hasOne(PublicSiteCountry::className(), ['country_code' => 'code']);
    }

    /**
     * @return CountryDao
     */
    public static function dao()
    {
        return CountryDao::me();
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param string $indexBy
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $indexBy = 'code'
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy,
            $select = 'name',
            $orderBy = ['order' => SORT_ASC],
            $where = ['in_use' => 1]
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->code);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/dictionary/country/edit', 'id' => $id]);
    }
}