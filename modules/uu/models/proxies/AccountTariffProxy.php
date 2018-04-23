<?php

namespace app\modules\uu\models\proxies;

use app\modules\uu\models\AccountTariff;

class AccountTariffProxy extends AccountTariff
{
    /*
     * Проксируемое значение "Даты отключения", полученное присоединением таблицы uu_account_tariff_log,
     * где условиями запроса являются:
     *
     * 1. uu_account_tariff_log.tariff_period_id IS NULL
     * 2. группировка по полю uu_account_tariff_log.account_tariff_id
     * 3. минимальное значенение из всех записей по полю uu_account_tariff_log.insert_time
     * */
    public $uu_account_tariff_log_actual_from_utc_disc;

    /*
     * Проксируемое значение "Даты создания", полученное присоединением таблицы client_contragent
     */
    public $client_contragent_created_at;

    /*
     * Проксируемое значение "Дата включения на тестовый тариф", полученное присоединением таблиц
     * uu_tariff_period и uu_tariff, где условиями запроса являются:
     *
     * 1. uu_account_tariff_log.tariff_period_id IS NOT NULL
     * 2. uu_tarif.tariff_status_id IN (4, 9)
     * 3. группировка по полю uatl.account_tariff_id
     * 4. минимальное значение из всех записей по полю uatl.actual_from_utc
     */
    public $uu_account_tariff_log_actual_from_utc_test;
}