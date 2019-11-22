<?php

namespace app\modules\sbisTenzor\classes;

/**
 * Статус сгенерированного черновика пакета документов
 *
 */
class SBISGeneratedDraftStatus
{
    const CANCELLED = 10;
    const DRAFT = 20;
    const PROCESSING = 30;
    const DONE = 40;
    const ERROR = 50;

    protected static $states = [
        self::CANCELLED => 'Отменён',
        self::DRAFT => 'Черновик',
        self::PROCESSING => 'В обработке',
        self::DONE => 'Обработан',
        self::ERROR => 'Ошибка',
    ];

    protected static $icons = [
        self::CANCELLED => 'glyphicon-remove',
        self::DRAFT => 'glyphicon-save-file',
        self::PROCESSING => 'В glyphicon-refresh',
        self::DONE => 'glyphicon-ok',
        self::ERROR => 'glyphicon-alert',
    ];

    protected static $textClass = [
        self::CANCELLED => 'text-muted',
        self::DRAFT => 'text-primary',
        self::PROCESSING => 'В text-warning',
        self::DONE => 'text-success',
        self::ERROR => 'text-danger',
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
    public static function getIconById($state)
    {
        return self::$icons[$state];
    }

    /**
     * @param int $state
     * @return string
     */
    public static function getTextClassById($state)
    {
        return self::$textClass[$state];
    }
}