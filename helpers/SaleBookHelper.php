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
     * Определяет, относится ли строка счета к телеком-услугам.
     *
     * Наличие $filter и его флагов регулирует,
     * какие типы услуг мы ищем:
     *
     *  - $filter->is_register      => ищем только ВАТС;
     *  - $filter->is_register_vp   => ищем ВАТС и ТС;
     *  - отсутствие $filter        => ищем ВАТС и ТС.
     *
     * Под "ВАТС" и "ТС" здесь понимаются:
     *  - ВАТС:  ServiceType::ID_VPBX;
     *  - ТС:    ServiceType::ID_VOIP.
     *
     * @param InvoiceLine         $line
     * @param SaleBookFilter|null $filter
     *
     * @return bool
     */
    public static function isTelephonyService(InvoiceLine $line, ?SaleBookFilter $filter = null): bool
    {

        $allowedServiceTypeIds = [];
        $textMarkers           = [];

        $hasRegisterFlags = $filter && ($filter->is_register || $filter->is_register_vp);

        if ($hasRegisterFlags) {
            // Есть фильтр реестра — смотрим флаги
            if ($filter->is_register_vp) {
                // ВАТС + ТС
                $allowedServiceTypeIds = [ServiceType::ID_VPBX, ServiceType::ID_VOIP];
                $textMarkers           = ['ВАТС', 'Телефон'];
            } elseif ($filter->is_register) {
                // Только ВАТС
                $allowedServiceTypeIds = [ServiceType::ID_VPBX];
                $textMarkers           = ['ВАТС'];
            }
        } else {
            // Фильтра нет — ищем и ВАТС, и ТС
            $allowedServiceTypeIds = [ServiceType::ID_VPBX, ServiceType::ID_VOIP];
            $textMarkers           = ['ВАТС', 'Телефон'];
        }

        // Если по какой-то причине ничего не настроили — явно говорим "нет"
        if (!$allowedServiceTypeIds && !$textMarkers) {
            return false;
        }

        //Проверка по типу услуги (service_type_id)
        if ($line->line && $line->line->id_service) {
            $serviceTypeId = $line->line->accountTariff->service_type_id ?? null;

            if ($serviceTypeId && in_array($serviceTypeId, $allowedServiceTypeIds, true)) {
                return true;
            }

            // Если сервис есть, но тип не входит в разрешённые — сразу нет
            return false;
        }

        $item = (string)$line->item;
        if ($item === '') {
            return false;
        }

        foreach ($textMarkers as $marker) {
            if ($marker !== '' && mb_stripos($item, $marker) !== false) {
                return true;
            }
        }

        return false;
    }
}
