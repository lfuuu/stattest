<?php

namespace app\models;

use app\classes\behaviors\ActualizeNumberByStatus;
use app\classes\behaviors\ActualizeVoipNumber;
use app\classes\behaviors\important_events\UsageAction;
use app\classes\behaviors\UsageDateTime;
use app\classes\behaviors\UsageVoipActualToDependencyPackage;
use app\classes\behaviors\UsageVoipAddress;
use app\classes\bill\VoipBiller;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\VoipServiceDao;
use app\helpers\usages\LogTariffTrait;
use app\helpers\usages\UsageVoipHelper;
use app\models\usages\UsageInterface;
use app\models\usages\UsageLogTariffInterface;
use app\queries\UsageVoipQuery;
use DateTime;
use yii\base\InvalidParamException;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $region
 * @property string $actual_from
 * @property string $actual_to
 * @property string $client
 * @property string $type_id
 * @property string $activation_dt
 * @property string $expire_dt
 * @property string $E164
 * @property int $no_of_lines
 * @property string $status
 * @property string $address
 * @property int $address_from_datacenter_id
 * @property int $edit_user_id
 * @property int $is_trunk
 * @property string $created
 * @property int $one_sip
 * @property int $line7800_id
 * @property string $create_params
 * @property int $prev_usage_id
 * @property int $next_usage_id
 * @property int $ndc_type_id
 *
 * @property-read TariffVoip $tariff
 * @property-read ClientAccount $clientAccount
 * @property-read Region $connectionPoint
 * @property-read Region $regionName
 * @property-read Number $voipNumber
 * @property-read Datacenter $datacenter
 * @property-read UsageVoip $line7800
 * @property-read UsageVoipPackage $packages
 * @property-read UsageVoipHelper $helper
 */
class UsageVoip extends ActiveRecord implements UsageInterface, UsageLogTariffInterface
{

    use LogTariffTrait;

    public static $allowedDirection = [
        'full' => 'Все',
        'russia' => 'Россия',
        'localmob' => 'Внутр. мобил.',
        'blocked' => 'Заблокированы',
        'local' => 'Внутр.',
    ];

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'UsageVoipAddress' => UsageVoipAddress::class,
            'ActualizeNumberByStatus' => ActualizeNumberByStatus::class,
            'ActualizeVoipNumber' => ActualizeVoipNumber::class,
            'ActiveDateTime' => UsageDateTime::class,
            'UsageVoipActualToDependPackage' => UsageVoipActualToDependencyPackage::class,
            'ImportantEvents' => UsageAction::class,
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_voip';
    }

    /**
     * @return UsageVoipQuery
     */
    public static function find()
    {
        return new UsageVoipQuery(get_called_class());
    }

    /**
     * @return \app\dao\UsageDao
     */
    public static function dao()
    {
        return VoipServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return VoipBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VoipBiller($this, $date, $clientAccount);
    }

    /**
     * @param string $date
     * @return bool|TariffVoip
     */
    public function getTariff($date = 'now')
    {
        $logTariff = $this->getLogTariff($date);
        if (!($logTariff instanceof LogTarif)) {
            return false;
        }

        return TariffVoip::findOne($logTariff->id_tarif);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_VOIP;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['client' => 'client']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConnectionPoint()
    {
        return $this->hasOne(Region::class, ['id' => 'region']);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $timezone = $this->clientAccount->timezone;

        $now = new DateTime('now', $timezone);

        $actualFrom = new DateTime($this->actual_from, $timezone);
        $actualTo = new DateTime($this->actual_to, $timezone);
        $actualTo->setTime(23, 59, 59);

        return $actualFrom <= $now and $actualTo >= $now;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoipNumber()
    {
        return $this->hasOne(Number::class, ['number' => 'E164']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::class, ['region' => 'region']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegionName()
    {
        return $this->hasOne(Region::class, ['id' => 'region']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLine7800()
    {
        return $this->hasOne(self::class, ['id' => 'line7800_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(UsageVoipPackage::class, ['usage_voip_id' => 'id']);
    }

    /**
     * @return UsageVoipHelper
     */
    public function getHelper()
    {
        return new UsageVoipHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoLogTariff(self::class);
    }

    /**
     * @return mixed
     */
    public function getAbonPerMonth()
    {
        return $this->tariff->month_number + ($this->tariff->month_line * ($this->no_of_lines - 1));
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     * @throws InvalidParamException
     */
    public static function getUrlById($id)
    {
        return Url::to(['/usage/voip/edit', 'id' => $id]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->E164;
    }

}
