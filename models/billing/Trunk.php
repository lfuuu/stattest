<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\TrunkDao;
use Yii;

/**
 * @property int $id
 * @property string $name
 * @property int $server_id
 * @property int $code
 */
class Trunk extends ActiveRecord
{
    const TRUNK_DIRECTION_ORIG = 'orig_enabled';
    const TRUNK_DIRECTION_TERM = 'term_enabled';
    const TRUNK_DIRECTION_BOTH = 'both_enabled'; // Только для условий

    public static $trunkTypes = [
        self::TRUNK_DIRECTION_BOTH => 'Ориг. / Терм.',
        self::TRUNK_DIRECTION_ORIG => 'Ориг.',
        self::TRUNK_DIRECTION_TERM => 'Терм.',
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
            'server_id' => 'Сервер',
            'code' => 'Код',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @return TrunkDao
     */
    public static function dao()
    {
        return TrunkDao::me();
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

}