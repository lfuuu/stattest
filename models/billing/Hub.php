<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id serial NOT NULL
 * @property string $name character varying(50) NOT NULL,
 * @property int $market_place_id integer NULL
 *
 * @property-read Server[] $servers
 */
class Hub extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    // Move vars below after creating MarketPlace model
    const MARKET_PLACE_ID_RUSSIA = 1;
    const MARKET_PLACE_ID_EUROPE = 2;

    public static $marketPlaces = [
        self::MARKET_PLACE_ID_RUSSIA => 'Россия',
        self::MARKET_PLACE_ID_EUROPE => 'Европа',
    ];

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'market_place_id' => 'Биржа звонков',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.hub';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServers()
    {
        return $this->hasMany(Server::class, ['hub_id' => 'id'])
            ->inverseOf('hub');
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s', $this->name);
    }
}
