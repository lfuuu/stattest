<?php

namespace app\classes\transfer;

use Yii;
use app\helpers\DateTimeZoneHelper;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\UsageIpRoutes;
use app\models\UsageTechCpe;
use app\models\UsageIpPorts;
use yii\db\ActiveRecord;

class IpPortsServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageIpPorts[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageIpPorts::find()
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

    /**
     * Процесс переноса услуги
     *
     * @return ActiveRecord
     * @throws \Exception
     */
    public function process()
    {
        $targetService = parent::process();

        $this->processRoutes($targetService);
        $this->processDevices($targetService);
        LogTarifTransfer::process($this, $targetService->id);

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function fallback()
    {
        $this->fallbackRoutes();
        $this->fallbackDevices();
        LogTarifTransfer::fallback($this);

        parent::fallback();
    }

    /**
     * Перенос связанных с услугой сетей
     *
     * @param ActiveRecord $targetService
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function processRoutes($targetService)
    {
        $routes =
            UsageIpRoutes::find()
                ->andWhere(['port_id' => $this->service->id])
                ->andWhere('actual_from <= :dateFrom', [':dateFrom' => $this->getActualDate()])
                ->andWhere('actual_to >= :dateFrom', [':dateFrom' => $this->getActualDate()])
                ->all();

        foreach ($routes as $route) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                /** @var ActiveRecord $targetRoute */
                $targetRoute = new $route;
                $targetRoute->setAttributes($route->getAttributes(), false);
                unset($targetRoute->id);
                $targetRoute->actual_from = $this->getActualDate();
                $targetRoute->activation_dt = $this->getActivationDatetime();
                $targetRoute->port_id = $targetService->id;

                $targetRoute->save();

                $route->actual_to = $this->getExpireDate();
                $route->expire_dt = $this->getExpireDatetime();

                $route->save();

                $dbTransaction->commit();
            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Отмена переноса связанных с услугой сетей
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function fallbackRoutes()
    {
        $routes =
            UsageIpRoutes::find()
                ->andWhere(['port_id' => $this->service->id])
                ->all();

        foreach ($routes as $route) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $movedRoute =
                    UsageIpRoutes::find()
                        ->andWhere(['port_id' => $this->service->next_usage_id])
                        ->one();
                Assert::isObject($movedRoute);

                $route->actual_to = $movedRoute->actual_to;
                $route->save();

                $movedRoute->delete();
                $dbTransaction->commit();
            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Перенос связанных с услугой устройств
     *
     * @param ActiveRecord $targetService
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function processDevices($targetService)
    {
        $devices =
            UsageTechCpe::find()
                ->andWhere(['service' => 'usage_ip_ports'])
                ->andWhere(['id_service' => $this->service->id])
                ->andWhere('actual_from <= :dateFrom', [':dateFrom' => $this->getActualDate()])
                ->andWhere('actual_to >= :dateFrom', [':dateFrom' => $this->getActualDate()])
                ->all();

        foreach ($devices as $device) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                /** @var ActiveRecord $targetDevice */
                $targetDevice = new $device;
                $targetDevice->setAttributes($device->getAttributes(), false);
                unset($targetDevice->id);
                $targetDevice->actual_from = $this->getActualDate();
                $targetDevice->id_service = $targetService->id;

                $targetDevice->save();

                $device->actual_to = $this->getExpireDate();

                $device->save();

                $dbTransaction->commit();
            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Отмена переноса связанных с услугой устройств
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function fallbackDevices()
    {
        $devices =
            UsageTechCpe::find()
                ->andWhere(['service' => 'usage_ip_ports'])
                ->andWhere(['id_service' => $this->service->id])
                ->all();

        foreach ($devices as $device) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $movedDevice =
                    UsageTechCpe::find()
                        ->andWhere(['service' => 'usage_ip_ports'])
                        ->andWhere(['id_service' => $this->service->next_usage_id])
                        ->andWhere('actual_from > :date', [':date' => date(DateTimeZoneHelper::DATE_FORMAT)])
                        ->one();
                Assert::isObject($movedDevice);

                $device->actual_to = $movedDevice->actual_to;
                $device->save();

                $movedDevice->delete();
                $dbTransaction->commit();
            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

}