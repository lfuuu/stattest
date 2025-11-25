<?php

namespace app\helpers;

use app\models\InvoiceLine;
use app\models\filter\SaleBookFilter;
use app\modules\uu\models\ServiceType;

/**
 * Вспомогательные функции для логики книги продаж / реестра.
 */
class SaleBookHelper
{
    /**
     * Определяет, относится ли строка счета к телеком-услугам (ВАТС / VOIP / телефония).
     *
     * Используется:
     *  - в книге продаж — для разделения обычных 0% и "НДС 0% (агент)";
     *  - в реестре — для отбора строк по галочкам "регистр" / "регистр ВАТС+VOIP".
     *
     * Поведение:
     *  - если передан $filter и в нём выставлены флаги is_register / is_register_vp,
     *    используется логика из views/report/accounting/sale-book/register.php;
     *  - иначе — общий "телефонный" детектор для книги продаж.
     *
     * @param InvoiceLine        $line
     * @param SaleBookFilter|null $filter
     *
     * @return bool
     */
    public static function isTelephonyService(InvoiceLine $line, ?SaleBookFilter $filter = null): bool
    {
        /**
         * 1) Режим РЕЕСТРА (когда стоит галочка "регистр" / "регистр ВАТС+VOIP")
         *    Логика 1 в 1 перенесена из views/report/accounting/sale-book/register.php
         */
        if ($filter && ($filter->is_register || $filter->is_register_vp)) {
            // Есть привязанный сервис (по id_service / accountTariff->service_type_id)
            if ($line->line && $line->line->id_service) {
                $serviceTypeId = $line->line->accountTariff->service_type_id ?? null;

                // Только ВАТС
                if ($filter->is_register && $serviceTypeId === ServiceType::ID_VPBX) {
                    return true;
                }

                // ВАТС + VOIP
                if (
                    $filter->is_register_vp
                    && in_array($serviceTypeId, [ServiceType::ID_VPBX, ServiceType::ID_VOIP], true)
                ) {
                    return true;
                }

                return false;
            }

            // Фолбэк — по тексту item
            $item = (string)$line->item;

            // Только ВАТС
            if ($filter->is_register && strpos($item, 'ВАТС') !== false) {
                return true;
            }

            // ВАТС + Телефон
            if (
                $filter->is_register_vp
                && (strpos($item, 'ВАТС') !== false || strpos($item, 'Телефон') !== false)
            ) {
                return true;
            }

            return false;
        }

        /**
         * 2) Обычный режим (книга продаж)
         *    Обобщённый детектор телеком-услуг.
         */

        // Привязанный сервис
        if ($line->line && $line->line->id_service) {
            $serviceTypeId = $line->line->accountTariff->service_type_id ?? null;

            return in_array($serviceTypeId, [ServiceType::ID_VPBX, ServiceType::ID_VOIP], true);
        }

        // Фолбэк — по тексту позиции
        $item = (string)$line->item;
        if ($item === '') {
            return false;
        }

        return
            (mb_stripos($item, 'ВАТС') !== false) ||
            (mb_stripos($item, 'Телефон') !== false);
    }
}
