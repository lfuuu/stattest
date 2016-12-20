<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\dao\CountryDao;
use app\classes\traits\GridSortTrait;

/**
 * @property int $code
 * @property string $alpha_3
 * @property string $name
 * @property int $in_use
 * @property string $lang
 * @property string $currency_id
 * @property integer $prefix
 * @property integer $order
 */
class Country extends ActiveRecord
{

    use GridSortTrait;

    const RUSSIA = 643;
    const HUNGARY = 348;
    const GERMANY = 276;
    const SLOVAKIA = 703;
    const AUSTRIA = 40;
    const CZECH = 203;

    const PREFIX_RUSSIA = 7;
    const PREFIX_HUNGARY = 36;
    const PREFIX_GERMANY = 49;
    const PREFIX_SLOVAKIA = 421;
    const PREFIX_AUSTRIA = 43;
    const PREFIX_CZECH = 420;

    public static $primaryField = 'code';

    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'code' => 'ID',
            'alpha_3' => 'Сокращение',
            'name' => 'Название',
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
            [['alpha_3', 'name', 'currency_id', 'lang', 'site'], 'string'],
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
     * @return CountryDao
     */
    public static function dao()
    {
        return CountryDao::me();
    }

    /**
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($isWithEmpty = false, $indexBy = 'code')
    {
        $list = self::find()
            ->where(['in_use' => 1])
            ->orderBy(['order' => SORT_ASC])
            ->indexBy($indexBy)
            ->all();

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
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
        return self::getUrlById($this->code);
    }

    /**
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/dictionary/country/edit', 'id' => $id]);
    }
}