<?php

namespace app\modules\sbisTenzor\classes;

/**
 * Статус пакета документов (внутренний и во внешней системе СБИС)
 *
 */
class SBISDocumentStatus
{
    const CANCELLED = 5;
    const CANCELLED_AUTO = 6;
    const CREATED = 10;
    const CREATED_AUTO = 11;
    const PROCESSING = 15;
    const SIGNED = 20;
    const SAVED = 30;
    const NOT_SIGNED = 35;
    const READY = 40;
    const SENT = 50;
    const SENT_ERROR = 51;
    const DELIVERED = 60;
    const NEGOTIATED = 61;
    const ERASED = 62;
    const ACCEPTED = 70;
    const OTHER = 80;
    const ERROR = 90;

    const EXTERNAL_EDIT = 0;
    const EXTERNAL_SENT = 3;
    const EXTERNAL_DELIVERED = 4;
    const EXTERNAL_ERROR = 6;
    const EXTERNAL_SUCCESS = 7;
    const EXTERNAL_NEGOTIATED = 9;
    const EXTERNAL_ERASED = 20;

    protected static $states = [
        self::CANCELLED => 'Отменён',
        self::CANCELLED_AUTO => 'Отменён (авто)',
        self::CREATED => 'Создан',
        self::CREATED_AUTO => 'Создан (авто)',
        self::PROCESSING => 'Отправляется',
        self::SIGNED => 'Подписан',
        self::SAVED => 'Сохранён',
        self::NOT_SIGNED => 'Ожидает подписи',
        self::READY => 'Готов к отправке',
        self::SENT => 'Отправлен',
        self::SENT_ERROR => 'Ошибка при отправке',
        self::DELIVERED => 'Прочитан',
        self::NEGOTIATED => 'Требует уточнений, не принят',
        self::ERASED => 'Удалён контрагентом',
        self::ACCEPTED => 'Подтверждён',
        self::OTHER => 'Неизвестный',
        self::ERROR => 'Ошибка',
    ];

    protected static $externalStates = [
        self::EXTERNAL_EDIT => 'Документ редактируется',
        self::EXTERNAL_SENT => 'Отправлен',
        self::EXTERNAL_DELIVERED => 'Доставлен',
        self::EXTERNAL_ERROR => 'Ошибка',
        self::EXTERNAL_SUCCESS => 'Выполнение завершено успешно',
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
     * @param int $externalState
     * @return string
     */
    public static function getExternalById($externalState)
    {
        return !empty(self::$externalStates[$externalState]) ? self::$externalStates[$externalState] : '';
    }
}