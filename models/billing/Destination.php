<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Expression;

/**
 * @property int $id serial NOT NULL
 * @property int $server_id integer NOT NULL
 * @property string $name character varying(50) NOT NULL,
 * @property int[] $prefixlist_ids integer[] NOT NULL DEFAULT '{}'::integer[],
 */
class Destination extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'server_id' => 'Точка присоединения',
            'name' => 'Название',
            'prefixlist_ids' => 'Префиксы',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.destination';
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
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param int $serverId
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $serverId = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = [
                'server_id' => SORT_ASC,
                'name' => SORT_ASC,
            ],
            $where = $serverId ?
                ['IN', 'server_id', [$serverId, 0]] :
                []
        );
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%d / %s', $this->server_id, $this->name);
    }
}
