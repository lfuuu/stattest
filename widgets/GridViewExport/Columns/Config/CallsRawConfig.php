<?php

namespace app\widgets\GridViewExport\Columns\Config;

use app\widgets\GridViewExport\Columns\Config;

class CallsRawConfig extends Config
{
    /**
     * @inheritDoc
     */
    public function getColumnsConfig()
    {
        return [
            'stats_nnp_package_minute_id' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'text',
                    'value' => 'packageMinutesText',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
        ];
    }
}