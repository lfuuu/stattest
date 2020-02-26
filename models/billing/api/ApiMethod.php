<?php

namespace app\models\billing\api;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetListTrait;
use Yii;

/**
 * @property int $id
 * @property int $api_id
 * @property string $method_sig
 * @property string $name
 * @property string $description
 */
class ApiMethod extends ActiveRecord
{
    use GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_api.api_method';
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


}