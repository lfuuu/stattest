<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;

/**
 * Класс переноса услуг типа "Телефония номера"
 * @package app\classes\transfer
 */
class VoipServiceTransfer extends ServiceTransfer
{

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process()
    {
        $targetService = parent::process();

        LogTarifTransfer::process($this, $targetService->id);

        $this->process7800($targetService);
        $this->processPackages($targetService);

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     */
    public function fallback()
    {
        LogTarifTransfer::fallback($this);

        parent::fallback();

        $this->fallback7800();
        $this->fallbackPackages();
    }

    /**
     * Перенос связанных с услугой линий без номер, если услуга 7800
     * @param object $targetService - базовая услуга
     */
    private function process7800($targetService)
    {
        if (!$targetService->line7800_id) {
            return;
        }

        $line7800 = UsageVoip::findOne($targetService->line7800_id);
        Assert::isObject($line7800);

        $this->service = $line7800;
        $targetService7800 = parent::process();
        $targetService->line7800_id = $targetService7800->id;
        $targetService->save();

        LogTarifTransfer::process($this, $targetService7800->id);
    }

    /**
     * Перенос связанных с услугой пакетов
     * @param object $targetService - базовая услуга
     */
    private function processPackages($targetService)
    {
        $packages =
            UsageVoipPackage::find()
                ->andWhere(['usage_voip_id' => $this->service->id])
                ->andWhere(['<=', 'actual_from', $this->getExpireDate()])
                ->andWhere(['>=', 'actual_to', $this->getExpireDate()])
                ->all();

        if (!count($packages)) {
            return;
        }

        foreach ($packages as $package) {
            $package->transferHelper
                ->setUsageVoip($targetService)
                ->setTargetAccount($targetService->clientAccount)
                ->setActivationDate($targetService->actual_from)
                ->process();
        }
    }

    /**
     * Отмена переноса связанных с услугой линий без номера, если услуга 7800
     */
    private function fallback7800()
    {
        if (!$this->service->line7800_id) {
            return;
        }

        $line7800 = UsageVoip::findOne($this->service->line7800_id);
        Assert::isObject($line7800);

        $this->service = $line7800;
        LogTarifTransfer::fallback($this);

        parent::fallback();
    }

    /**
     * Отмена переноса связанных с услугой пакетов
     */
    private function fallbackPackages()
    {
        $packages =
            UsageVoipPackage::find()
                ->andWhere(['usage_voip_id' => $this->service->id])
                ->all();

        if (!count($packages)) {
            return;
        }

        foreach ($packages as $package) {
            $package->transferHelper
                ->setTargetAccount($this->service->clientAccount)
                ->fallback();
        }
    }

}
