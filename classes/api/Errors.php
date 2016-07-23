<?php

namespace app\classes\api;


/**
 * Class Errors
 *
 * Класс ошибок api
 *
 * @package app\classes\api
 */
class Errors
{
    const ERROR_INTERNAL = 501; //Произошла внутренняя ошибка. Для заказа свяжитесь с менеджером.
    const ERROR_EMAIL_ALREADY = 502; //E-mail уже зарегистрирован. Оформите приобретение номера через Личный Кабинет.
    const ERROR_RESERVE = 503; // Произошла ошибка резерва. Для заказа свяжитесь с менеджером.const
    const ERROR_EXECUTE = 504; // Произошла ошибка выполнения запроса. Возможно вы не дождались выполнения предыдущего. (блокировка)
    const ERROR_TIMEOUT = 505; // Ваша заявка поставлена в очередь, с вами свяжется менеджер.
    const ERROR_RESERVE_NUMBER_BUSY = 506; // Один из выбранных номеров уже зарезервирован другим клиентом
}