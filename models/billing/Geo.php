<?php
namespace app\models\billing;

use app\dao\billing\GeoDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 */
class Geo extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.geo';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function dao()
    {
        return GeoDao::me();
    }

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($isWithEmpty = false)
    {
        $list = self::find()
            ->where(['<', 'id', 1000000000])
            ->orderBy(['name' => SORT_ASC])
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
        return $this->name;// . ' ' . ($this->prefix? strtr($this->prefix, ['{' => '', '}' => '']): $this->id);
    }
}