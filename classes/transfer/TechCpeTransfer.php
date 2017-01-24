<?php

namespace app\classes\transfer;

use Yii;
use yii\db\ActiveRecord;
use app\classes\Assert;
use yii\base\InvalidValueException;
use app\models\ClientAccount;
use app\models\UsageTechCpe;

/**
 * Класс переноса устройств
 */
class TechCpeTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageTechCpe[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageTechCpe::find()
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->andWhere(['id_service' => 0])
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
        if ((int)$this->service->next_usage_id) {
            throw new InvalidValueException('Услуга уже перенесена');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            /** @var ActiveRecord $targetService */
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);
            unset($targetService->id);
            $targetService->actual_from = $this->getActualDate();
            $targetService->prev_usage_id = $this->service->id;
            $targetService->client = $this->targetAccount->client;

            $targetService->save();

            $this->service->actual_to = $this->getExpireDate();
            $this->service->next_usage_id = $targetService->id;

            $this->service->save();

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

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
        if (!(int)$this->service->next_usage_id) {
            throw new InvalidValueException('Услуга не была подготовлена к переносу');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            /** @var ActiveRecord $movedService */
            $movedService = new $this->service;
            $movedService = $movedService->findOne($this->service->next_usage_id);
            Assert::isObject($movedService);

            $this->service->next_usage_id = 0;
            $this->service->actual_to = $movedService->actual_to;

            $this->service->save();

            $movedService->delete();
            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

}