<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetListTrait;

/**
 * @property int $id
 * @property string $name
 * @property string $route_name
 */
class A2pSmsRoute extends ActiveRecord
{
    use GetListTrait;

    public static function tableName()
    {
        return 'auth.a2psms_route';
    }

    /**
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return \Yii::$app->dbPg;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'route_name'], 'string'],
        ];
    }
}