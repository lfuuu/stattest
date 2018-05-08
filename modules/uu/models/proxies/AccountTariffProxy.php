<?php

namespace app\modules\uu\models\proxies;

use app\modules\uu\models\AccountTariff;

class AccountTariffProxy extends AccountTariff
{
    /*
     * Проксируемое значение "Даты создания", полученное присоединением таблицы client_contragent
     */
    public $client_contragent_created_at;
}