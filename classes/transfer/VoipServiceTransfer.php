<?php

namespace app\classes\transfer;

use app\classes\Html;
use app\models\ClientAccount;
use app\models\Usage;
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

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     */
    public function fallback()
    {
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
