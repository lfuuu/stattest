<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Html;
use app\models\Usage;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\LogTarif;

/**
 * Класс переноса услуг типа "Виртуальная АТС"
 * @package app\classes\transfer
 */
class VirtpbxServiceTransfer extends ServiceTransfer
{

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process()
    {
        $targetService = parent::process();

        $this->processVoipNumbers($targetService);
        LogTarifTransfer::process($this, $targetService->id);

        return $targetService;
    }

    /**
     * Перенос связанных с услугой voip номеров
     * @param object $targetService - базовая услуга
     */
    private function processVoipNumbers($targetService)
    {
        foreach ($this->service->clientAccount->voipNumbers as $number => $options) {
            if ($options['type'] != 'vpbx' || $options['stat_product_id'] != $this->service->id) {
                continue;
            }

            if (
                (
                $usage =
                    UsageVoip::find()
                        ->where([
                            'E164' => $number,
                            'client' => $this->service->clientAccount->client,
                            'next_usage_id' => 0,
                        ])
                        ->actual()
                        ->one()
                ) instanceof Usage
            ) {
                $dbTransaction = Yii::$app->db->beginTransaction();
                try {
                    $targetUsage = new $usage;
                    $targetUsage->setAttributes($usage->getAttributes(), false);
                    unset($targetUsage->id);
                    $targetUsage->activation_dt = $this->getActivationDatetime();
                    $targetUsage->actual_from = $this->getActualDate();
                    $targetUsage->prev_usage_id = $usage->id;
                    $targetUsage->client = $targetService->clientAccount->client;

                    $targetUsage->save();

                    $usage->expire_dt = $this->getExpireDatetime();
                    $usage->actual_to = $this->getExpireDate();
                    $usage->next_usage_id = $targetUsage->id;

                    $usage->save();

                    $usageTransfer =
                        $usage
                            ->getTransferHelper($usage)
                            ->setActivationDate($usage->actual_from);

                    LogTarifTransfer::process($usageTransfer, $targetUsage->id);

                    $dbTransaction->commit();
                } catch (\Exception $e) {
                    $dbTransaction->rollBack();
                    throw $e;
                }
            }
        }
    }

    public function getTypeTitle()
    {
        return 'Виртуальная АТС';
    }

    public function getTypeHelpBlock()
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
        $value = $this->service->currentTariff ? $this->service->currentTariff->description : 'Описание';
        $description = [];
        $checkboxOptions = [];

        $numbers = $this->service->clientAccount->voipNumbers;

        foreach ($numbers as $number => $options) {
            if ($options['type'] != 'vpbx' || $options['stat_product_id'] != $this->service->id) {
                continue;
            }
            $description[] = $number;
        }

        return [$value, implode(', ', $description), $checkboxOptions];
    }

}
