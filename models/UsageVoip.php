<?php
namespace app\models;

use app\classes\bill\VoipBiller;
use app\classes\transfer\VoipServiceTransfer;
use app\dao\services\VoipServiceDao;
use yii\db\ActiveRecord;
use app\queries\UsageVoipQuery;
use DateTime;

/**
 * @property int $id
 *
 * @property Region $connectionPoint
 * @property ClientAccount $clientAccount
 * @property
 */
class UsageVoip extends ActiveRecord implements Usage
{

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
            'UsageVoipAddress' => \app\classes\behaviors\UsageVoipAddress::className(),
            'ActualizeNumberByStatus' => \app\classes\behaviors\ActualizeNumberByStatus::className(),
            'ActualizeVoipNumber' => \app\classes\behaviors\ActualizeVoipNumber::className(),
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

    public function getTariff()
    {
        return null;
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
        return $this->hasOne(VoipNumber::className(), ['number' => 'E164']);
    }

    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::className(), ["region" => "region"]);
    }

    public function getCurrentLogTariff($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d H:i:s');
        }

        return
            LogTarif::find()
                ->andWhere(['service' => 'usage_voip'])
                ->andWhere(['id_service' => $this->id])
                ->andWhere('date_activation<=:date', [':date' => $date])
                ->andWhere('id_tarif!=0')
                ->orderBy('date_activation desc, id desc')
                ->limit(1)
                ->one();
    }

    public function getCurrentTariff()
    {
        $logTariff = $this->getCurrentLogTariff();
        if ($logTariff === null) {
            return false;
        }

        $tariff = TariffVoip::findOne($logTariff->id_tarif);
        return $tariff;
    }

    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }
    
    public static function getTransferHelper($usage)
    {
        return new VoipServiceTransfer($usage);
    }

    public function getAbonPerMonth()
    {
        return $this->currentTariff->month_number + ($this->currentTariff->month_line * ($this->no_of_lines - 1));
    }

    public function getUsagePackages()
    {
        return $this->hasMany(UsageVoipPackage::className(), ['usage_voip_id' => 'id']);
    }

}

