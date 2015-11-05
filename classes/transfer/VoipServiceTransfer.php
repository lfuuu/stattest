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

    public function getTypeTitle()
    {
        return 'Телефония номера';
    }

    public function getTypeHelpBlock()
    {
        return Html::tag(
            'div',
            'Заблокированные номера подключены на ВАТС,<br >' .
            'перенос возможен только совместно с ВАТС.<br />' .
            'Отключить номер от ВАТС можно в ЛК',
            [
                'style' => 'background-color: #F9F0DF; font-size: 11px; font-weight: bold; padding: 5px; margin-top: 10px; white-space: nowrap;',
            ]
        );
    }

    public function getTypeDescription()
    {
        $value = $this->service->E164 . ' (линий ' . $this->service->no_of_lines . ')';
        $description = '';
        $checkboxOptions = [];

        $numbers = $this->service->clientAccount->voipNumbers;
        if (isset($numbers[ $this->service->E164 ]) && $numbers[ $this->service->E164]['type'] == 'vpbx') {
            if (($usage = UsageVirtpbx::findOne($numbers[ $this->service->E164 ]['stat_product_id'])) instanceof Usage) {
                $description = $usage->currentTariff->description . ' (' . $usage->id . ')';
            }
            $checkboxOptions['disabled'] = 'disabled';
        }

        return [$value, $description, $checkboxOptions];
    }

}
