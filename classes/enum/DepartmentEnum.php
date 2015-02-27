<?php
namespace app\classes\enum;

use app\classes\Enum;

class DepartmentEnum extends Enum
{
    const SALES      = 'sales';
    const ACCOUNTING = 'accounting';
    const TECHNICAL  = 'technical';

    protected static $names = [
        self::SALES      => 'Отдел продаж',
        self::ACCOUNTING => 'Бухгалтерия',
        self::TECHNICAL  => 'Техподдержка',
    ];
}