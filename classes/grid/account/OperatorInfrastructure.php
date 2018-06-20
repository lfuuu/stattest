<?php

namespace app\classes\grid\account;

use app\models\BusinessProcess;
use app\models\Business;

class OperatorInfrastructure extends AccountGrid
{
    /* Использование трейта, который генерирует массив объектов GenericFolder, исходя из текущего контекста BusinessProcessStatus */
    use GenericFolderTrait;

    public function getBusiness()
    {
        return Business::OPERATOR;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::OPERATOR_INFRASTRUCTURE;
    }

	/**
	 * @return array
	 */
	public function getColumns()
	{
		return [
			'status',
			'id',
			'company',
			'contractNo',
			'contract_created',
			'currency',
			'manager',
			'account_manager',
			'region',
			'federal_district',
			'contract_type',
			'financial_type',
		];
	}
}
