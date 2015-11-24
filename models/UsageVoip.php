<?php
namespace app\models;

use DateTime;
use yii\db\ActiveRecord;
use app\classes\bill\VoipBiller;
use app\classes\transfer\VoipServiceTransfer;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\VoipServiceDao;
use app\queries\UsageVoipQuery;
use app\models\usages\UsageInterface;
use app\models\usages\UsageLogTariffInterface;
use app\helpers\usages\LogTariffTrait;
use app\helpers\usages\UsageVoipHelper;

/**
 * @property int $id
 *
 * @property Region $connectionPoint
 * @property ClientAccount $clientAccount
 * @property
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

    public function behaviors()
    {
        return [
            'UsageVoipAddress' =>        \app\classes\behaviors\UsageVoipAddress::className(),
            'ActualizeNumberByStatus' => \app\classes\behaviors\ActualizeNumberByStatus::className(),
            'ActualizeVoipNumber' =>     \app\classes\behaviors\ActualizeVoipNumber::className(),
        ];
    }

    public static function tableName()
    {
        return 'usage_voip';
    }

    public static function find()
    {
        return new UsageVoipQuery(get_called_class());
    }

    public static function dao()
    {
        return VoipServiceDao::me();
    }

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

    public function getServiceType()
    {
        return Transaction::SERVICE_VOIP;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function isActive()
    {
        $timezone = $this->clientAccount->timezone;

        $now = new DateTime('now', $timezone);

        $actualFrom = new DateTime($this->actual_from, $timezone);
        $actualTo = new DateTime($this->actual_to, $timezone);
        $actualTo->setTime(23, 59, 59);

        return $actualFrom <= $now and $actualTo >= $now;
    }

    public function getVoipNumber()
    {
        return $this->hasOne(Number::className(), ['number' => 'E164']);
    }

    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::className(), ["region" => "region"]);
    }

    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    /**
     * @param $usage
     * @return VoipServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new VoipServiceTransfer($usage);
    }

    /**
     * @return UsageVoipHelper
     */
    public function getHelper()
    {
        return new UsageVoipHelper($this);
    }

    public function getAbonPerMonth()
    {
        return $this->tariff->month_number + ($this->tariff->month_line * ($this->no_of_lines - 1));
    }

    public function getUsagePackages()
    {
        return $this->hasMany(UsageVoipPackage::className(), ['usage_voip_id' => 'id']);
    }

    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoLogTariff(self::className());
    }

}

