<?php

namespace app\widgets\GridViewExport\Columns\Config;

use app\modules\uu\models\AccountTariff;
use app\widgets\GridViewExport\Columns\Config;

class AccountTariffConfig extends Config
{
    /**
     * @inheritDoc
     */
    public function getColumnsConfig()
    {
        return [
            'tariff_period_id' => [
                'format' => 'text',
                'value' => 'name',
            ],
            'prev_account_tariff_tariff_id' => [
                'format' => 'text',
                'value' => 'prevAccountTariff.tariffPeriod.name',
            ],
            'tariff_country_id' => [
                'format' => 'text',
                'contentOptions' => null,
                'value' => 'tariffPeriod.tariff.countriesText',
            ],
            'tariff_organization_id' => [
                'format' => 'text',
                'contentOptions' => null,
                'value' => 'tariffPeriod.tariff.organizationsText',
            ],
            'client_account_id' => [
                'format' => 'text',
                'value' => 'clientAccount.accountTypeAndId',
            ],
            'comment' => [
                'format' => 'text',
                'value' => 'comment',
            ],
        ];
    }
}