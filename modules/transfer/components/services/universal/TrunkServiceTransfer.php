<?php

namespace app\modules\transfer\components\services\universal;

use app\classes\Assert;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use app\models\UsageTrunk;
use app\models\UsageTrunkSettings;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\Processor;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use DateTime;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

class TrunkServiceTransfer extends BasicServiceTransfer
{

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_TRUNK;
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     */
    public function finalizeClose(PreProcessor $preProcessor)
    {
        // Ничего не делать в случае переноса "Универсальная услуга" => "Универсальная услуга"
        // Для универсальных услуг метод переопределяет родительский, ОБЯЗАТЕЛЬНО ДОЛЖЕН НИЧЕГО НЕ ДЕЛАТЬ
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws InvalidValueException
     * @throws InvalidParamException
     * @throws InvalidCallException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function finalizeOpen(PreProcessor $preProcessor)
    {
        parent::finalizeOpen($preProcessor);

        // Try to process packages
        $this->_packagesProcess($preProcessor);

        // Complete transfer AccountTariff => UsageTrunk
        if ($preProcessor->targetClientAccount->account_version > $preProcessor->clientAccount->account_version) {
            // Regular service to universal

            /** @var UsageTrunk $sourceService */
            $sourceService = $preProcessor->sourceServiceHandler->getService();

            $this->_createUsageTrunk($sourceService, $this->getService());
        } elseif (
            $preProcessor->targetClientAccount->account_version == $preProcessor->clientAccount->account_version
            && $preProcessor->targetClientAccount->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL
        ) {
            // Universal service to universal
            /** @var UsageTrunk $sourceService */
            $sourceService = UsageTrunk::findOne(['id' => $preProcessor->sourceServiceHandler->getService()->id]);
            Assert::isObject($sourceService);

            $sourceService->expire_dt = $preProcessor->expireDatetime;
            $sourceService->actual_to = $preProcessor->expireDate;
            $sourceService->next_usage_id = $this->getService()->primaryKey;

            if (!$sourceService->save()) {
                throw new ModelValidationException($sourceService);
            }

            $this->_createUsageTrunk($sourceService, $this->getService());
        }
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws InvalidCallException
     * @throws \Exception
     */
    private function _packagesProcess(PreProcessor $preProcessor)
    {
        $packages = AccountTariff::find()
            ->where(['prev_account_tariff_id' => $this->getService()->prev_usage_id])
            // Active packages only
            ->andWhere(['IS NOT', 'tariff_period_id', null]);

        if ($packages->count()) {
            /** @var AccountTariff $package */
            foreach ($packages->each() as $package) {
//                if ($package->tariffPeriod->tariff->is_default) {
                    // Skip if package is default
//                    continue;
//                }

                $preProcessor->processor->run(
                    (new PreProcessor)
                        ->setProcessor(new $preProcessor->processor)
                        ->setServiceType(Processor::SERVICE_PACKAGE)
                        ->setService(
                            $preProcessor->processor->getHandler(Processor::SERVICE_PACKAGE),
                            $package->id
                        )
                        ->setProcessedFromDate($preProcessor->activationDate)
                        ->setSourceClientAccount($preProcessor->clientAccount->id)
                        ->setTargetClientAccount($preProcessor->targetClientAccount->id)
                        ->setTariff($package->tariff_period_id)
                        ->setRelation('prev_account_tariff_id', $this->getService()->primaryKey)
                );
            }
        }
    }

    /**
     * @param UsageTrunk $sourceUsageTrunk
     * @param AccountTariff $accountTariff
     * @throws ModelValidationException
     */
    private function _createUsageTrunk(UsageTrunk $sourceUsageTrunk, AccountTariff $accountTariff)
    {
        /** @var AccountTariffLog $targetServiceAccountTariffLog */
        $targetServiceAccountTariffLog = reset($accountTariff->accountTariffLogs);
        $actualFrom = (new DateTime($targetServiceAccountTariffLog->actual_from_utc))
            ->modify('+1 day')
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        /** @var UsageInterface|ActiveRecord $regularService */
        $regularService = new UsageTrunk;
        $regularService->setAttributes($sourceUsageTrunk->getAttributes(null, ['id', 'next_usage_id']));
        $regularService->id = $accountTariff->id;
        $regularService->actual_from = $actualFrom;
        $regularService->actual_to = UsageInterface::MAX_POSSIBLE_DATE;

        if (!$regularService->save()) {
            throw new ModelValidationException($regularService);
        }

        // Clone trunk settings
        /** @var UsageTrunkSettings $setting */
        foreach ($sourceUsageTrunk->settings as $setting) {
            /** @var UsageTrunkSettings $trunkSetting */
            $trunkSetting = new $setting;
            $trunkSetting->setAttributes($setting->getAttributes(null, ['id']));
            $trunkSetting->usage_id = $regularService->id;

            if (!$trunkSetting->save()) {
                throw new ModelValidationException($trunkSetting);
            }
        }
    }

}
