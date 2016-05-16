<?php
namespace app\classes\enum;

use app\classes\Enum;

class VoipRegistrySourceEnum extends Enum
{
    const PORTABILITY = 'portability';
    const OPERATOR = 'operator';
    const REGULATOR = 'regulator';

    public static $names = [
        self::PORTABILITY => 'Portability',
        self::OPERATOR => 'Operator',
        self::REGULATOR => 'Regulator',
    ];
}