<?php
namespace app\classes\enum;

use app\classes\Enum;

class TariffStatusEnum extends Enum
{
    const STATUS_PUBLIC  = 'public';
    const STATUS_SPECIAL = 'special';
    const STATUS_ARCHIVE = 'archive';

    protected static $names = [
        self::STATUS_PUBLIC  => 'Публичный',
        self::STATUS_SPECIAL => 'Специальный',
        self::STATUS_ARCHIVE => 'Архивный'
    ];
}
