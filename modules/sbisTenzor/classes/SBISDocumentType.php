<?php

namespace app\modules\sbisTenzor\classes;

/**
 * Пакет документов в системе СБИС
 *
 */
class SBISDocumentType
{
    // документы реализации
    const SHIPPED_IN = 1;
    const SHIPPED_OUT = 2;
    // акты сверки
    const RECONCILIATION_IN = 3;
    const RECONCILIATION_OUT = 4;
    // договоры
    const CONTRACT_IN = 5;
    const CONTRACT_OUT = 6;
    // корреспонденция
    const CORRESPONDENCE_IN = 7;
    const CORRESPONDENCE_OUT = 8;

    protected static $types = [
        self::SHIPPED_IN => 'ДокОтгрВх',
        self::SHIPPED_OUT => 'ДокОтгрИсх',
        // акты сверки
        self::RECONCILIATION_IN => 'АктСверВх',
        self::RECONCILIATION_OUT => 'АктСверИсх',
        // договоры
        self::CONTRACT_IN => 'ДоговорВх',
        self::CONTRACT_OUT => 'ДоговорИсх',
        // корреспонденция
        self::CORRESPONDENCE_IN => 'КоррВх',
        self::CORRESPONDENCE_OUT => 'КоррИсх',
    ];

    /**
     * @param $typeId
     * @return string
     */
    public static function getById($typeId)
    {
        return self::$types[$typeId];
    }
}