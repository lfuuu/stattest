<?php
namespace app\models\billing;

use app\dao\billing\CallsDao;
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
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
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
     * @return []
     */
    public static function getListOrderBy()
    {
        return [
            'server_id' => SORT_ASC,
            'name' => SORT_ASC,
        ];
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
