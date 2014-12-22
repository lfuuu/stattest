<?php
namespace app\classes\enum;

use app\classes\Enum;

class ServiceTypeEnum extends Enum
{
    const INTERNET    = 'internet';
    const VOIP        = 'voip';
    const VIRTUAL_ATS = 'virtual_ats';

    protected static $names = [
        self::INTERNET    => 'Интернет',
        self::VOIP        => 'Телефония',
        self::VIRTUAL_ATS => 'Виртуальная АТС',
    ];
}