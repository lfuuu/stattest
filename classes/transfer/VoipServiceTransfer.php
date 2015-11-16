<?php

namespace app\classes\transfer;

use app\classes\Html;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\Usage;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;

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
        $targetService = parent::process();

        LogTarifTransfer::process($this, $targetService->id);
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

}
