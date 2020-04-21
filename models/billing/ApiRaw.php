<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\CallsDao;
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
 * @property int $id
 * @property int $server_id
 * @property bool $orig
 * @property int $cdr_id
 * @property int $connect_time
 * @property int $account_id
 * @property int $api_id
 * @property int $api_method_id
 * @property int $service_api_id
 * @property int $api_pricelist_id
 * @property int $api_pricelist_item_id
 * @property int $api_count
 * @property float $api_weight
 * @property float $rate
 * @property float $cost
 * @property int $nnp_package_api_id
 * @property int $account_tariff_light_id
 * @property string $mcn_api_call_uuid
 */
class ApiRaw extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'api_raw.api_raw';
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

    public function getAccountTariffLight()
    {
        return $this->hasOne(AccountTariffLight::class, ['id' => 'account_tariff_light_id']);
    }
}
