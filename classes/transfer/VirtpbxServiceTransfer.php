<?php

namespace app\classes\transfer;

use Yii;
use app\models\usages\UsageInterface;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use yii\db\ActiveRecord;

/**
 * Класс переноса услуг типа "Виртуальная АТС"
 */
class VirtpbxServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageVirtpbx[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageVirtpbx::find()
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

    /**
     * Процесс переноса
     *
     * @return UsageInterface
     * @throws \Exception
     */
    public function process()
    {
        $targetService = parent::process();

        $this->_processVoipNumbers($targetService);
        LogTarifTransfer::process($this, $targetService->id);

        return $targetService;
    }

    /**
     * Перенос связанных с ВАТС номеров
     *
     * @param ActiveRecord $targetService
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function _processVoipNumbers($targetService)
    {
        foreach ($this->service->clientAccount->voipNumbers as $number => $options) {
            if ($options['type'] !== 'vpbx' || $options['stat_product_id'] != $this->service->id) {
                continue;
            }

            if (
                (
                $usage = UsageVoip::find()
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
