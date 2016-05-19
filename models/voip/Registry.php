<?php
namespace app\models\voip;

use app\classes\behaviors\CreatedAt;
use app\dao\VoipRegistryDao;
use app\models\City;
use yii\db\ActiveRecord;

/**
 * Class Registry
 *
 * @property integer id
 * @property integer country_id
 * @property integer city_id
 * @property string source
 * @property integer number_type_id
 * @property string number_from
 * @property string number_to
 * @property integer account_id
 * @property string created_at
 * @property string status
 * @property City city
 *
 * @package app\models\voip
 */
class Registry extends ActiveRecord
{
    const STATUS_EMPTY = 'empty';
    const STATUS_PARTLY = 'partly';
    const STATUS_FULL = 'full';

    public static $names = [
        self::STATUS_EMPTY => 'Пусто',
        self::STATUS_PARTLY => 'Частично',
        self::STATUS_FULL => 'Заполнено',
    ];

    public function behaviors()
    {
        return [
            'CreatedAt' => CreatedAt::className()
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '#',
            'country_id' => 'Страна',
            'city_id' => 'Город',
            'source' => 'Источник',
            'number_type_id' => 'Тип номера',
            'number_from' => 'Номер "с"',
            'number_to' => 'Номер "по"',
            'account_id' => 'ЛС',
            'created_at' => 'Создано',
        ];
    }

    public static function tableName()
    {
        return 'voip_registry';
    }

    public static function dao()
    {
        return VoipRegistryDao::me();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return self::dao()->getStatus($this);
    }

    /**
     * @inheritdoc
     */
    public function getPassMap()
    {
        return self::dao()->getPassMap($this);
    }

    /**
     * @inheritdoc
     */
    public function fillNumbers()
    {
        return self::dao()->fillNumbers($this);
    }

    /**
     * @inheritdoc
     */
    public function getStatusInfo()
    {
        return self::dao()->getStatusInfo($this);
    }

    /**
     * @inheritdoc
     */
    public function toSale()
    {
        return self::dao()->toSale($this);
    }
}
