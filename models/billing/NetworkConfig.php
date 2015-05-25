<?php
namespace app\models\billing;

use app\dao\billing\NetworkConfigDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class NetworkConfig extends ActiveRecord
{
    public static function tableName()
    {
        return 'voip.network_config';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return NetworkConfigDao::me();
    }
}