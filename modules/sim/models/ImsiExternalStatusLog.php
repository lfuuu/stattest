<?php

namespace app\modules\sim\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\modules\sim\classes\externalStatusLog\StatusContentRecognition;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * @property int $imsi
 * @property string $insert_dt
 * @property string $status
 * @property-readonly string $insertDate
 */
class ImsiExternalStatusLog extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.sim_imsi_external_status_log';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    public function getInsertDate()
    {
        return DateTimeZoneHelper::getDateTime($this->insert_dt);
    }

    public function getStatusString()
    {
        return StatusContentRecognition::me()->getAsString($this);
    }
}
