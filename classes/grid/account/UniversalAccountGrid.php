<?php

namespace app\classes\grid\account;

use app\models\BusinessProcess;


class UniversalAccountGrid extends AccountGrid
{
    use GenericFolderTrait;

    private ?int $businessProcessId = null;
    private ?int $businessId = null;

    /**
     * @return int
     */
    public function getBusiness()
    {
        return $this->businessId;
    }

    /**
     * @return int
     */
    public function getBusinessProcessId(): int
    {
        return $this->businessProcessId;
    }

    public function setBusinessProcessId($businessProcessId)
    {
        $this->businessProcessId = $businessProcessId;
        $this->businessId = BusinessProcess::find()->where(['id' => $this->businessProcessId])->select('business_id')->scalar();

        if (!$this->businessId) {
            throw new \LogicException('businessId not set');
        }

        return $this;
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager',
            'account_manager',
            'region',
            'legal_entity',
        ];
    }
}