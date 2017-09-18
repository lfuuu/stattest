<?php

namespace app\models;

use app\classes\bill\VirtpbxBiller;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\VirtpbxServiceDao;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\helpers\usages\LogTariffTrait;
use app\helpers\usages\UsageVirtpbxHelper;
use app\models\usages\UsageInterface;
use app\models\usages\UsageLogTariffInterface;
use app\modules\uu\models\AccountTariff;
use app\queries\UsageQuery;
use DateTime;

/**
 * @property int $id
 * @property int $region
 * @property int $amount
 * @property int $is_unzipped
 * @property string $comment
 * @property-read TariffVirtpbx $tariff
 * @property-read ClientAccount $clientAccount
 * @property-read UsageVirtpbxHelper $helper
 */
class UsageVirtpbx extends ActiveRecord implements UsageInterface, UsageLogTariffInterface
{

    use LogTariffTrait;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
            'UpdateTask' => [
                'class' => \app\classes\behaviors\UpdateTask::className(),
                'model' => self::tableName(),
            ]
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_virtpbx';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return VirtpbxServiceDao
     */
    public static function dao()
    {
        return VirtpbxServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return VirtpbxBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VirtpbxBiller($this, $date, $clientAccount);
    }

    /**
     * @param string $date
     * @return bool|TariffVirtpbx
     */
    public function getTariff($date = 'now')
    {
        $logTariff = $this->getLogTariff($date);
        if ($logTariff === null) {
            return false;
        }

        return TariffVirtpbx::findOne($logTariff->id_tarif);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_VIRTPBX;
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    /**
     * @return Region
     */
    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    /**
     * @return UsageVirtpbxHelper
     */
    public function getHelper()
    {
        return new UsageVirtpbxHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoLogTariff(self::className());
    }

    /**
     * Переоткрытие услуги
     *
     * @return UsageVirtpbx
     * @throws \Exception
     */
    public function reopen()
    {
        if (strtotime($this->expire_dt) > time()) {
            throw new \LogicException('Услуга активна');
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $newUsage = new UsageVirtpbx;
            $newUsage->setAttributes($this->getAttributes(null, ['id']), false);
            $newUsage->actual_from = date(DateTimeZoneHelper::DATE_FORMAT);
            $newUsage->actual_to = UsageInterface::MAX_POSSIBLE_DATE;

            if (!$newUsage->save()) {
                throw new ModelValidationException($newUsage);
            }

            /** @var LogTarif $logTariff */
            $logTariff = $this->getLogTariff();

            if (!$logTariff) {
                throw new \LogicException('Тариф не найден');
            }

            $newLogTariff = new LogTarif();
            $newLogTariff->setAttributes($logTariff->getAttributes(null, ['id']), false);
            $newLogTariff->id_service = $newUsage->id;
            $newLogTariff->date_activation = date(DateTimeZoneHelper::DATE_FORMAT);
            if (!$newLogTariff->save()) {
                throw new ModelValidationException($newLogTariff);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $newUsage;
    }

}
