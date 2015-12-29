<?php

namespace app\classes\transfer;

use Yii;
use app\models\usages\UsageInterface;
use app\models\ClientAccount;
use app\models\UsageVoip;

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
                            'type_id' => 'number',
                        ])
                        ->actual()
                        ->one()
                ) instanceof UsageInterface
            ) {
                $dbTransaction = Yii::$app->db->beginTransaction();
                try {
                    $usage::getTransferHelper($usage)
                        ->setTargetAccount($targetService->clientAccount)
                        ->setActivationDate($targetService->actual_from)
                        ->process();

                    $dbTransaction->commit();
                } catch (\Exception $e) {
                    $dbTransaction->rollBack();
                    throw $e;
                }
            }
        }
    }

}
