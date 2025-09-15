<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\CallsDao;
use app\models\ClientAccount;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use app\modules\nnp\models\Region;
use app\modules\nnp\models\City;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * @property string $id                serial,
 * @property string $event_id          uuid not null
 * @property string $primary key,
 * @property string $event_type        text,
 * @property string $agent_id          integer,
 * @property string $agent_name        text,
 * @property string $account_id        text,
 * @property string $service_type_id   integer,
 * @property string $account_tariff_id bigint,
 * @property string $duration          integer,
 * @property string $action_start      timestamp with time zone,
 * @property string $action_end        timestamp with time zone,
 * @property string $event_ts          timestamp with time zone,
 * @property string $event_name        text,
 * @property string $event_version     integer,
 * @property string $processed_at      timestamp with time zone default CURRENT_TIMESTAMP
 * 
 * @property-read ClientAccount $clientAccount
 */
class AiDialogRaw extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'ai_dialog_raw.raw';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'account_id']);
    }
}
