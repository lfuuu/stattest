<?php
namespace app\models;

use app\classes\traits\GridSortTrait;
use app\dao\CountryDao;
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
     * @return CountryDao
     */
    public static function dao()
    {
        return CountryDao::me();
    }

    /**
     * @param bool $isWithEmpty
     * @param string $indexBy
     * @return Country[]
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
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/dictionary/country/edit', 'id' => $id]);
    }
}