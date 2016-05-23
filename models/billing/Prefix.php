<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property string $prefix
 * @property integer $geo_id
 * @property boolean $mob
 * @property integer $dest
 * @property integer $region
 * @property integer $operator_id
 *
 * @property Geo $geo
 */
class Prefix extends ActiveRecord
{
    public static function tableName()
    {
        return 'geo.prefix';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['id' => 'geo_id']);
    }

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @return string[]
     */
    public static function getList($isWithEmpty = false, $maxIdLength = 6)
    {
        $query = (new \yii\db\Query())
            ->select(['prefix.prefix', 'geo.name'])
            ->from(self::tableName())
            ->innerJoin(Geo::tableName(), 'prefix.geo_id = geo.id')
            ->where(['<=', 'LENGTH(prefix.prefix)', $maxIdLength])// для оптимизации
        ;

        $list = [];
        if ($isWithEmpty) {
            $list[''] = '----';
        }
        foreach ($query->all(self::getDb()) as $row) {
            $list[$row['prefix']] = $row['name'];
        }

        return $list;
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return (string)($this->geo ?: $this->id);
    }
}