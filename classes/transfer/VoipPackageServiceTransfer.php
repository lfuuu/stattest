<?php

namespace app\classes\transfer;

use ActiveRecord\ModelException;
use app\models\usages\UsageInterface;
use Yii;
use yii\base\InvalidValueException;
use app\models\ClientAccount;

/**
 * Класс переноса услуг типа "Телефония номера. Пакет"
 * @package app\classes\transfer
 */
class VoipPackageServiceTransfer extends ServiceTransfer
{

    /** @var UsageInterface $usageVoip */
    private $usageVoip = null;

    /**
     * @param UsageInterface $usage
     * @return $this
     */
    public function setUsageVoip($usage)
    {
        $this->usageVoip = $usage;
        return $this;
    }

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process()
    {
        if ((int)$this->service->next_usage_id) {
            throw new InvalidValueException('Услуга уже перенесена');
        }

        if (is_null($this->usageVoip)) {
            throw new InvalidValueException('Отсутствует обязательная услуга телефонии');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);
            unset($targetService->id);
            $targetService->actual_from = $this->usageVoip->actual_from;
            $targetService->actual_to = $this->usageVoip->actual_to;
            $targetService->prev_usage_id = $this->service->id;
            $targetService->usage_voip_id = $this->usageVoip->id;
            $targetService->client = $this->targetAccount->client;

            if (!$targetService->save()) {
                throw new ModelException('Cant\'t save target voip package model');
            }

            $this->service->next_usage_id = $targetService->id;

            if (!$this->service->save()) {
                throw new ModelException('Cant\'t update original voip package model');
            }

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

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

}