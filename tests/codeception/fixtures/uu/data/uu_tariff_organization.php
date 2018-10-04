<?php

use app\models\Organization;
use app\modules\uu\models\Tariff;

$organizationIds = [Organization::MCN_TELECOM, Organization::MCN_TELECOM_RETAIL];
$data = [];
for ($i = 1; $i <= 14; $i++) {
    foreach ($organizationIds as $organizationId) {
        $data[] = [
            'tariff_id' => Tariff::DELTA + $i,
            'organization_id' => $organizationId,
        ];
    }
}
return $data;