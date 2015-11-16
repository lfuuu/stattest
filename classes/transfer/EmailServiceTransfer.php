<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Assert;
use yii\base\InvalidValueException;

/**
 * Класс переноса услуг типа "E-mail"
 * @package app\classes\transfer
 */
class EmailServiceTransfer extends ServiceTransfer
{

    /**
     * Перенос базовой сущности услуги
     * @return object - созданная услуга
     */
    public function process()
    {
        if ((int) $this->service->next_usage_id)
            throw new InvalidValueException('Услуга уже перенесена');

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
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
        }
        catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     * @throws Exception
     */
    public function fallback()
    {
        if (!(int) $this->service->next_usage_id)
            throw new InvalidValueException('Услуга не была подготовлена к переносу');

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $movedService = new $this->service;
            $movedService = $movedService->findOne($this->service->next_usage_id);
            Assert::isObject($movedService);

            $this->service->next_usage_id = 0;
            $this->service->actual_to = $movedService->actual_to;

            $this->service->save();

            $movedService->delete();
            $dbTransaction->commit();
        }
        catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

}