<?php

namespace app\classes\transfer;

use Yii;
use app\models\ClientAccount;

/**
 * Класс переноса услуг типа "E-mail"
 * @package app\classes\transfer
 */
class EmailServiceTransfer extends ServiceTransfer
{

    public function process(ClientAccount $targetAccount)
    {
        if ((int) $this->service->dst_usage_id)
            throw new \Exception('Услуга уже перенесена');

        $dbTransaction = Yii::$app->db->beginTransaction();
        try
        {
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);
            unset($targetService->id);
            $targetService->actual_from = date('Y-m-d', $this->activation_date);
            $targetService->src_usage_id = $this->service->id;
            $targetService->client = $targetAccount->client;

            $targetService->save();

            $this->service->actual_to = date('Y-m-d', $this->activation_date - 1);
            $this->service->dst_usage_id = $targetService->id;

            $this->service->save();

            $dbTransaction->commit();
        }
        catch (\Exception $e)
        {
            $dbTransaction->rollBack();
            throw $e;
        }

        return $targetService;
    }

}