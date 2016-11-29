<?php

namespace app\classes\traits;

trait UsageTrait
{

    /**
     * Возвращает связь по которой услуга связана с лицевым счетом
     * @return array
     */
    public static function getClientAccountLink()
    {
        // Поле в услуге, Поле в лицевом счете
        return ['client', 'client'];
    }

}
