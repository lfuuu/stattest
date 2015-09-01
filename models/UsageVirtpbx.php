<?php
namespace app\models;

use app\classes\Html;
use app\classes\bill\VirtpbxBiller;
use app\classes\transfer\VirtpbxServiceTransfer;
use app\dao\services\VirtpbxServiceDao;
use app\queries\UsageQuery;
use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id

 * @property TariffVirtpbx $tariff
 * @property
 */
class UsageVirtpbx extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'usage_virtpbx';
    }

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public static function dao()
    {
        return VirtpbxServiceDao::me();
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VirtpbxBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffVirtpbx::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_VIRTPBX;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getCurrentTariff()
    {
        $logTariff =
            LogTarif::find()
            ->andWhere(['service' => 'usage_virtpbx', 'id_service' => $this->id])
            ->andWhere('date_activation <= now()')
            ->andWhere('id_tarif != 0')
            ->orderBy('date_activation desc, id desc')
            ->limit(1)
            ->one();
        if ($logTariff === null) {
            return false;
        }

        $tariff = TariffVirtpbx::findOne($logTariff->id_tarif);
        return $tariff;
    }

    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function getTransferHelper()
    {
        return new VirtpbxServiceTransfer($this);
    }

    public static function getTypeTitle()
    {
        return 'Виртуальная АТС';
    }

    public static function getTypeHelpBlock()
    {
        return Html::tag(
            'div',
            'ВАТС переносится только с подключенными номерами. ' .
            'Отключить номера можно в настройках ВАТС',
            [
                'style' => 'background-color: #F9F0DF; font-size: 11px; font-weight: bold; padding: 5px; margin-top: 10px;',
            ]
        );
    }

    public function getTypeDescription()
    {
        $value = $this->currentTariff ? $this->currentTariff->description : 'Описание';
        $description = [];
        $checkboxOptions = [];

        $numbers = $this->clientAccount->voipNumbers;

        foreach ($numbers as $number => $options) {
            if ($options['type'] != 'vpbx' || $options['stat_product_id'] != $this->id) {
                continue;
            }
            $description[] = $number;
        }

        return [$value, implode(', ', $description), $checkboxOptions];
    }

}
