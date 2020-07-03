<?php

namespace app\modules\nnp2\media\related\Transformer;

use app\modules\nnp2\media\related\Transformer;

class RegionTransformer extends Transformer
{
    protected array $preProcessing = [
        [self::FUNC_PREG_REPLACE, '/.*\|/', ''],
        [self::FUNC_STR_REPLACE, 'область', 'обл.'],
        [self::FUNC_STR_REPLACE, 'г.о. ', '',],
        [self::FUNC_STR_REPLACE, 'р-н ', 'район ',],
        [self::FUNC_STR_REPLACE, 'город ', 'г. '],
        [self::FUNC_STR_REPLACE, 'автономный округ', 'АО'],
        [self::FUNC_STR_REPLACE, ' - ', '-'],
    ];
}
