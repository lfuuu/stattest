<?php

namespace app\modules\sim\classes;

/**
 * Статус заливки сим-карт
 *
 */
class RegistryState
{
    const CANCELLED = 0;
    const NEW = 10;
    const STARTED = 20;
    const PROCESSING = 30;
    const ERROR = 40;
    const COMPLETED = 50;

    protected static $states = [
        self::NEW => 'Новая',
        self::STARTED => 'Запущена',
        self::PROCESSING => 'В процессе',
        self::ERROR => 'Ошибка',
        self::COMPLETED => 'Завершена',
    ];

    /**
     * @param int $state
     * @return string
     */
    public static function getById($state)
    {
        return self::$states[$state];
    }
}