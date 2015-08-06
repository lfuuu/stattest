<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\UsageIpRoutes;
use app\models\TechCpe;

/**
 * Класс переноса услуг типа "IP Port"
 * @package app\classes\transfer
 */
class IpPortsServiceTransfer extends ServiceTransfer
{
    /*
    --  select * from usage_ip_ppp ?
    */

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
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
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
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
     * @param object $targetService - базовая услуга
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
            }
            catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Отмена переноса связанных с услугой сетей
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
            }
            catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Перенос связанных с услугой устройств
     * @param object $targetService - базовая услуга
     */
    private function processDevices($targetService)
    {
        $devices =
            TechCpe::find()
                ->andWhere(['service' => 'usage_ip_ports'])
                ->andWhere(['id_service' => $this->service->id])
                ->andWhere('actual_from <= :dateFrom', [':dateFrom' => $this->getActualDate()])
                ->andWhere('actual_to >= :dateFrom', [':dateFrom' => $this->getActualDate()])
                ->all();

        foreach ($devices as $device) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $targetDevice = new $device;
                $targetDevice->setAttributes($device->getAttributes(), false);
                unset($targetDevice->id);
                $targetDevice->actual_from = $this->getActualDate();
                $targetDevice->id_service = $targetService->id;

                $targetDevice->save();

                $device->actual_to = $this->getExpireDate();

                $device->save();

                $dbTransaction->commit();
            }
            catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Отмена переноса связанных с услугой устройств
     */
    private function fallbackDevices()
    {
        $devices =
            TechCpe::find()
                ->andWhere(['service' => 'usage_ip_ports'])
                ->andWhere(['id_service' => $this->service->id])
                ->all();

        foreach ($devices as $device) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $movedDevice =
                    TechCpe::find()
                        ->andWhere(['service' => 'usage_ip_ports'])
                        ->andWhere(['id_service' => $this->service->next_usage_id])
                        ->andWhere('actual_from > :date', [':date' => (new \DateTime())->format('Y-m-d')])
                        ->one();
                Assert::isObject($movedDevice);

                $device->actual_to = $movedDevice->actual_to;
                $device->save();

                $movedDevice->delete();
                $dbTransaction->commit();
            }
            catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }


}