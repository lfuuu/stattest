<?php

namespace app\modules\sbisTenzor\classes;

/**
 * Статус интеграции клиента со СБИС
 *
 */
class SBISExchangeStatus
{
    const UNKNOWN = 0; // default
    const PROBLEM = 10; // auto
    const DECLINED = 20;
    const SET_UP = 30; // auto
    const APPROVED = 40;

    public static $states = [
        self::UNKNOWN => 'Неизвестен',
        self::PROBLEM => 'Ошибки',
        self::DECLINED => 'Отклонён',
        self::SET_UP => 'Настроен',
        self::APPROVED => 'Подтверждён',
    ];

    protected static $fixed = [
        self::DECLINED,
        self::APPROVED,
    ];

    public static $notApproved = [
        self::PROBLEM,
        self::DECLINED,
    ];

    public static $verified = [
        self::SET_UP,
        self::APPROVED,
    ];

    /**
     * @param int $state
     * @return string
     */
    public static function getById($state)
    {
        return self::$states[$state];
    }

    /**
     * @param int $state
     * @return string
     */
    public static function isFixedById($state)
    {
        return in_array($state, self::$fixed);
    }

    /**
     * @param int $state
     * @return string
     */
    public static function isNotApprovedById($state)
    {
        return in_array($state, self::$notApproved);
    }

    /**
     * @param int $state
     * @return string
     */
    public static function isVerifiedById($state)
    {
        return in_array($state, self::$verified);
    }
}