<?php

namespace app\helpers;

use app\models\InvoiceLine;
use app\modules\uu\models\ServiceType;

/**
 * Вспомогательные функции для логики книги продаж.
 */
class SaleBookHelper
{
    /**
     * Определяет, относится ли строка счета к телеком-услугам (ВАТС / VOIP / телефония).
     *
     * Используется, чтобы решать:
     *  - попадёт строка в обычный 0%,
     *  - или в отдельную колонку "НДС 0% (агент)".
     *
     * @param InvoiceLine $line Объект строки счета.
     *
     * @return bool
     */
    public static function isTelephonyService(InvoiceLine $line): bool
    {
        if ($line->line && !empty($line->line->id_service)) {
            $serviceTypeId = $line->line->accountTariff->service_type_id ?? null;

            return in_array($serviceTypeId, [ServiceType::ID_VPBX, ServiceType::ID_VOIP], true);
        }

        $item = (string)$line->item;

        if ($item === '') {
            return false;
        }

        return
            (mb_stripos($item, 'ВАТС') !== false) ||
            (mb_stripos($item, 'Телефон') !== false);
    }
}
