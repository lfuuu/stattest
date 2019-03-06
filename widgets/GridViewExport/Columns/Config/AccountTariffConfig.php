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
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'value' => 'name',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
            'prev_account_tariff_tariff_id' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'value' => 'prevAccountTariff.tariffPeriod.name',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
            'tariff_country_id' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'contentOptions' => null,
                    'value' => 'tariffPeriod.tariff.countriesText',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
            'tariff_organization_id' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'contentOptions' => null,
                    'value' => 'tariffPeriod.tariff.organizationsText',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
            'client_account_id' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'value' => 'clientAccount.accountTypeAndId',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
            'comment' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'value' => 'comment',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
            'account_manager_name' => [
                Config::PARAM_NAME_KEYS => [],
                Config::PARAM_NAME_EAGER_FIELDS => 'clientAccount.clientContractModel.accountManagerUser',
            ],
            'date_sale' => [
                Config::PARAM_NAME_KEYS => [],
                Config::PARAM_NAME_EAGER_FIELDS => 'accountTariffHeap',
            ],
            'trouble_id' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'value' => 'accountTroublesText',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
        ];
    }
}