<?php

namespace app\modules\uu\tarificator;


/**
 * Предварительное списание (транзакции) абонентской платы. Тарификация. Пакеты без продления
 */
class AccountLogPeriodPackageTarificator extends AccountLogPeriodTarificator
{
    public $mode = 2; // 1 - main process, without price package, 2 - only price package
}
