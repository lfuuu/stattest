<?php

namespace app\modules\mtt\classes;

/**
 * Класс для удобства разбора ответа от MTT
 */
abstract class MttResponse
{
    /** @var string Например, 'getAccountBalance1508339067' */
    public $requestId;

    /** @var string Например, 'getAccountBalance' */
    public $method;

    /** @var string Например, 'ok' */
    public $status;

    /** @var array В зависимости от method */
    public $result;
}
