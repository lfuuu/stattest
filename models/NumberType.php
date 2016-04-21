<?php
namespace app\models;

use DateInterval;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 *
 * @property NumberTypeCountry[] $numberTypeCountries
 * @property DateInterval $hold
 *
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10715171
 */
class NumberType extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const ID_GEO_DID = 1;
    const ID_NON_GEO_DID = 2;
    const ID_INTERNATIONAL_DID = 3;
    const ID_INTERNAL = 4;
    const ID_EXTERNAL = 5;

    const DEFAULT_HOLD = '6 month';

    private static $holdList = [
        self::ID_EXTERNAL => '1 day'
    ];

    public static function tableName()
    {
        return 'voip_number_type';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
        ];
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
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/voip/number-type/edit', 'id' => $this->id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumberTypeCountries()
    {
        return $this->hasMany(NumberTypeCountry::className(), ['voip_number_type_id' => 'id'])
            ->indexBy('country_id');
    }

    /**
     * Возвращает время "отстоя" номера
     * @return \DateInterval
     */
    public function getHold()
    {
        if (isset(self::$holdList[$this->id])) {
            $interval = self::$holdList[$this->id];
        } else {
            $interval = self::DEFAULT_HOLD;
        }

        return \DateInterval::createFromDateString($interval);
    }

}