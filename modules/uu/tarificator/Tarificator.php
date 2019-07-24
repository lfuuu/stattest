<?php

namespace app\modules\uu\tarificator;

/**
 * Class Tarificator
 */
abstract class Tarificator
{
    protected $isEcho = false;

    protected static $descriptions = [
        AccountLogSetupTarificator::class => 'Плата за подключение',
        AccountLogPeriodTarificator::class => 'Абонентская плата',
        AccountLogResourceTarificator::class => 'Плата за ресурсы',
        AccountLogMinTarificator::class => 'Минималка за ресурсы',
        AccountEntryTarificator::class => 'Проводки',
        BillTarificator::class => 'Счета',
        FreePeriodInFinanceBlockTarificator::class => 'Не списывать абонентку и минималку при финансовой блокировке',
        BillConverterTarificator::class => 'Конвертировать счета в старую бухгалтерию',
        SetCurrentTariffTarificator::class => 'Обновить AccountTariff.TariffPeriod',
        SyncResourceTarificator::class => 'Отправить измененные ресурсы на платформу',
        RealtimeBalanceTarificator::class => 'RealtimeBalance',
        FinanceBlockTarificator::class => 'Месячную финансовую блокировку заменить на постоянную',
        AutoCloseAccountTariffTarificator::class => 'Автоматически закрыть услугу по истечению тестового периода',
    ];

    /**
     * @param bool $isEcho
     */
    public function __construct($isEcho = false)
    {
        $this->isEcho = $isEcho;
    }

    /**
     * Вывод на консоль строку
     *
     * @param string $string
     */
    protected function out($string)
    {
        if ($this->isEcho) {
            echo $string;
        }
    }

    public function getDescription()
    {
        $className = get_class($this);
        if (array_key_exists($className, static::$descriptions)) {
            return static::$descriptions[$className];
        }

        return 'Тарификатор класса ' . substr(strrchr($className, "\\"), 1);
    }

    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    abstract public function tarificate($accountTariffId = null);
}
