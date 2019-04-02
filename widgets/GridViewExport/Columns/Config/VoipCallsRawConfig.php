<?php

namespace app\widgets\GridViewExport\Columns\Config;

use app\widgets\GridViewExport\Columns\Config;

class VoipCallsRawConfig extends Config
{
    /**
     * @inheritDoc
     */
    public function getColumnsConfig()
    {
        return [
            'dst_operator_name' => [
                Config::PARAM_NAME_KEYS => [
                    'format' => 'raw',
                ],
                Config::PARAM_NAME_EAGER_FIELDS => [],
            ],
        ];
    }
}