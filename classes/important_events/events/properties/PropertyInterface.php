<?php

namespace app\classes\important_events\events\properties;

use app\models\important_events\ImportantEvents;

/**
 * @property mixed $value
 * @property string $description
 */
interface PropertyInterface
{

    /**
     * Конструктор
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event);

    /**
     * Получение списка доступных описаний переменных
     * @return array
     */
    public static function labels();

    /**
     * Получение списка доступных методов
     * @return array
     */
    public function methods();

    /**
     * Получение базового значения
     * @return mixed
     */
    public function getValue();

    /**
     * Получение полного форматированного значения
     * @return string
     */
    public function getDescription();
}