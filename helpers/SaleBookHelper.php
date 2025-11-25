<?php

namespace app\helpers;

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
     * @param mixed $line Объект строки счета (InvoiceLine / NewbillLine и т.п.)
     *
     * @return bool
     */
    public static function isTelephonyService($line): bool
    {
        try {
            if (isset($line->line) && $line->line && !empty($line->line->id_service)) {
                $serviceTypeId = $line->line->accountTariff->service_type_id ?? null;

                return in_array($serviceTypeId, [ServiceType::ID_VPBX, ServiceType::ID_VOIP], true);
            }

            $item = (string)($line->item ?? '');
            if ($item === '') {
                return false;
            }

            return
                (mb_stripos($item, 'ВАТС') !== false) ||
                (mb_stripos($item, 'Телефон') !== false) ||
                (mb_stripos($item, 'ТС') !== false);
        } catch (\Throwable $e) {
            // На всякий пожарный — никаких фаталов в отчёте
            return false;
        }
    }
}
