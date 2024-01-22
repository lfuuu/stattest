<?php

namespace app\classes\contragent\importer\lk;


use app\classes\contragent\importer\lk\typeFactory\CoreLkContragentTypeDefault;
use app\classes\contragent\importer\lk\typeFactory\CoreLkContragentTypeFactory;
use app\classes\HandlerLogger;
use app\classes\Singleton;
use app\classes\Utils;
use app\exceptions\ModelValidationException;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Organization;
use app\models\User;

class CoreLkContract extends Singleton
{
    public function applyChanges($paramJsonStr)
    {
        if (!is_array($paramJsonStr)) {
            $json = Utils::fromJson($paramJsonStr);
        } else {
            $json = $paramJsonStr;
        }

        if (!$json || !isset($json['contract_id']) || !isset($json['organization_id']) || !$json['contract_id'] || !$json['organization_id']) {
            throw new \InvalidArgumentException('JSON does not have the correct structure');
        }

        /** @var ClientContract $contract */
        $contract = ClientContract::find()->where(['id' => $json['contract_id']])->one();

        if (!$contract) {
            throw new \InvalidArgumentException('Contract not found');
        }

        $organization = $contract->organization;

        if (!$organization) {
            throw new \LogicException('Incorrect organization in contract');
        }

        $newOrganization = Organization::find()->byId($json['organization_id'])->actual()->one();

        /** @var Organization $newOrganization */
        if (!$newOrganization) {
            throw new \InvalidArgumentException('Incorrect organization');
        }

        $newOrganizationId = $newOrganization->organization_id;

        if ($newOrganizationId == $json['organization_id']) {
            HandlerLogger::me()->add('Organizations equal. No changes');
        }

        $origOrganization = $contract->organization_id;
        $contract->organization_id = $newOrganizationId;

        if (!$contract->save()) {
            throw new ModelValidationException($contract);
        }

        HandlerLogger::me()->add("Organizations changes. ({$origOrganization} => {$newOrganizationId})");
    }
}

