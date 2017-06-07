<?php
namespace app\classes\enum;

use app\classes\Enum;

class VoipRegistrySourceEnum extends Enum
{
    const PORTABILITY = 'portability';
    const OPERATOR = 'operator';
    const REGULATOR = 'regulator';
    const PORTABILITY_NOT_FOR_SALE = 'portability_not_for_sale';
    const OPERATOR_NOT_FOR_SALE = 'operator_not_for_sale';


    public static $names = [
        self::PORTABILITY => 'Portability',
        self::OPERATOR => 'Operator',
        self::REGULATOR => 'Regulator',
        self::PORTABILITY_NOT_FOR_SALE => 'Portability (Not for sale)',
        self::OPERATOR_NOT_FOR_SALE => 'Operator (Not for sale)'
    ];

    public static $service = [
        self::PORTABILITY_NOT_FOR_SALE => 'Portability (Not for sale)',
        self::OPERATOR_NOT_FOR_SALE => 'Operator (Not for sale)'
    ];
}