<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int id serial NOT NULL
 * @property int server_id integer NOT NULL
 * @property string name character varying(50) NOT NULL,
 * @property int[] prefixlist_ids integer[] NOT NULL DEFAULT '{}'::integer[],
 */
class Destination extends ActiveRecord
{
    /**
     * Вернуть имена полей
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

    public static function tableName()
    {
        return 'auth.destination';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * По какому полю сортировать для getList()
     * @return array
     */
    public static function getListOrderBy()
    {
        return [
            'server_id' => SORT_ASC,
            'name' => SORT_ASC,
        ];
    }

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @param int $serverId
     * @return self[]
     */
    public static function getList($isWithEmpty = false, $serverId = null)
    {
        $query = self::find();
        $serverId && $query->where(['IN', 'server_id', [$serverId, 0]]);
        $list = $query->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return sprintf('%d / %s', $this->server_id, $this->name);
    }
}
