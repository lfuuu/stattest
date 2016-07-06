<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string $param
 * @property string $value
 */
class Param extends ActiveRecord
{
    const PI_LIST_LAST_INFO = 'pi_list_last_info'; //информация о последнем импорте

    public static function tableName()
    {
        return 'params';
    }

    public static function setParam($key, $value)
    {
        $param = self::findOne(['param' => $key]);

        if (!$param) {
            $param = new Param;
            $param->param = $key;
        }

        $param->value = json_encode($value);
        return $param->save();
    }
}
