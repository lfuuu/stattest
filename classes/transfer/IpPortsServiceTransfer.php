<?php

namespace app\classes\transfer;

use Yii;
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

    public function process(ClientAccount $targetAccount) {
        $targetService = parent::process($targetAccount);

        $this->processRoutes($targetService);
        $this->processDevices($targetService);

        return $targetService;
    }

    private function processRoutes($targetService) {
        $routes =
            UsageIpRoutes::find()
                ->andWhere(['port_id' => $this->service->id])
                ->all();

        foreach ($routes as $route) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try
            {
                $targetRoute = new $route;
                $targetRoute->setAttributes($route->getAttributes(), false);
                unset($targetRoute->id);
                $targetRoute->actual_from = date('Y-m-d', $this->activation_date);
                $targetRoute->port_id = $targetService->id;

                $targetRoute->save();

                $route->actual_to = date('Y-m-d', $this->activation_date - 1);

                $route->save();

                $dbTransaction->commit();
            }
            catch (\Exception $e)
            {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    private function processDevices($targetService) {
        $devices =
            TechCpe::find()
                ->andWhere(['service' => 'usage_ip_ports'])
                ->andWhere(['id_service' => $this->service->id])
                ->all();

        foreach ($devices as $device) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try
            {
                $targetDevice = new $device;
                $targetDevice->setAttributes($device->getAttributes(), false);
                unset($targetDevice->id);
                $targetDevice->actual_from = date('Y-m-d', $this->activation_date);
                $targetDevice->id_service = $targetService->id;

                $targetDevice->save();

                $device->actual_to = date('Y-m-d', $this->activation_date - 1);

                $device->save();

                $dbTransaction->commit();
            }
            catch (\Exception $e)
            {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

}