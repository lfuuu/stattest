<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class UsageIpRoutes extends ActiveRecord
{

    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
        ];
    }

    public static function tableName()
    {
        return 'usage_ip_routes';
    }
}