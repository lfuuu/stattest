<?php
namespace app\classes\enum;

use app\classes\Enum;

class VoipRegistrySourceEnum extends Enum
{
    const OPERATOR = 'operator';
    const REGULATOR = 'regulator';
    const INNONET = 'innonet';
    const VOXBONE = 'voxbone';
    const G4M = 'g4m';
    const VOICECONNECT = 'voice_connect';
    const DETACHED = 'detached';
    const PORTABILITY_NOT_FOR_SALE = 'portability_not_for_sale';
    const PORTABILITY_INNONET = 'portability_innonet';
    const OPERATOR_NOT_FOR_SALE = 'operator_not_for_sale';
    const DIDWWW = 'didwww';
    const ONDERIA = 'Onderia';


    public static $names = [
        self::OPERATOR => 'Operator',
        self::REGULATOR => 'Regulator',
        self::INNONET => 'Innonet',
        self::VOXBONE => 'Voxbone',
        self::G4M => 'G4M',
        self::VOICECONNECT => 'VoiceConnect',
        self::DETACHED => 'Detached',
        self::PORTABILITY_NOT_FOR_SALE => 'Portability (Not for sale)',
        self::PORTABILITY_INNONET => 'Portability (Innonet)',
        self::OPERATOR_NOT_FOR_SALE => 'Operator (Not for sale)',
        self::DIDWWW => 'DIDWWW',
        self::ONDERIA => 'Onderia',
    ];

    public static $service = [
//        self::PORTABILITY_NOT_FOR_SALE => 'Portability (Not for sale)',
        self::PORTABILITY_INNONET => 'Portability (Innonet)',
        self::OPERATOR_NOT_FOR_SALE => 'Operator (Not for sale)'
    ];
}