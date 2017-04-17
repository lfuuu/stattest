<?php

namespace tests\codeception\unit\models;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Bill;
use app\modules\uu\tarificator\AutoCloseAccountTariffTarificator;
use app\modules\uu\tarificator\SetCurrentTariffTarificator;
use app\tests\codeception\fixtures\uu\AccountTariffFixture;
use app\tests\codeception\fixtures\uu\AccountTariffLogFixture;
use app\tests\codeception\fixtures\uu\TariffFixture;
use app\tests\codeception\fixtures\uu\TariffPeriodFixture;
use app\tests\codeception\fixtures\uu\TariffResourceFixture;
use DateTimeImmutable;
use tests\codeception\unit\custom\_TestCase;

/**
 * Class UbillingTest
 */
class UbillingTest extends _TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->unload();
        $this->load();
    }

    protected function load()
    {
        (new TariffFixture)->load();
        (new TariffPeriodFixture)->load();
        (new TariffResourceFixture)->load();
        (new AccountTariffFixture)->load();
        (new AccountTariffLogFixture)->load();

        ob_start();

        $setCurrentTariffTarificator = new SetCurrentTariffTarificator;
        $setCurrentTariffTarificator->tarificate(null, false);

        $autoCloseAccountTariffTarificator = new AutoCloseAccountTariffTarificator;
        $autoCloseAccountTariffTarificator->tarificate(null, false);

        ob_end_clean();
    }

    protected function unload()
    {
        AccountLogSetup::deleteAll();
        AccountLogPeriod::deleteAll();
        AccountLogResource::deleteAll();
        AccountLogMin::deleteAll();
        AccountEntry::deleteAll();
        Bill::deleteAll();
        (new AccountTariffLogFixture)->unload();
        (new AccountTariffFixture)->unload();
        (new TariffResourceFixture)->unload();
        (new TariffPeriodFixture)->unload();
        (new TariffFixture)->unload();
    }

    /**
     * Проверить, как смена тарифов конвертируется в "большие" диапазоны (по смене тарифов)
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogHugeFromToTariffs1()
    {
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 1])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogHugeFromToTariffs = $accountTariff->getAccountLogHugeFromToTariffs();
        $this->assertEquals(2, count($accountLogHugeFromToTariffs));

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазон 0)

        // диапазон 0
        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[0]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[0]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(1, $accountLogHugeFromToTariffs[0]->tariffPeriod->id);

        // 2го с 3го подключил месячный тариф
        // по этому тарифу с 3го до конца прошлого месяца и весь этот месяц (диапазон 1)

        // диапазон 1
        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[1]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEmpty($accountLogHugeFromToTariffs[1]->dateTo);

        $this->assertEquals(2, $accountLogHugeFromToTariffs[1]->tariffPeriod->id);
    }

    /**
     * Проверить, как смена тарифов конвертируется в "большие" диапазоны (по смене тарифов)
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogHugeFromToTariffs2()
    {
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 2])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogHugeFromToTariffs = $accountTariff->getAccountLogHugeFromToTariffs();
        $this->assertEquals(3, count($accountLogHugeFromToTariffs));

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазон 0)

        // диапазон 0
        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[0]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[0]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(1, $accountLogHugeFromToTariffs[0]->tariffPeriod->id);

        // 2го сразу же подключил месячный тариф
        // по этому тарифу со 2го до конца прошлого месяца (диапазон 1)

        // диапазон 1
        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[1]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[1]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(2, $accountLogHugeFromToTariffs[1]->tariffPeriod->id);

        // 4го сразу же подключил годовой тариф
        // по этому тарифу с 4го до конца этого месяца + еще 11 месяцев (диапазон 2)

        // диапазон 2
        $this->assertNotEmpty($accountLogHugeFromToTariffs[2]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+3 days')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[2]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEmpty($accountLogHugeFromToTariffs[2]->dateTo);

        $this->assertEquals(3, $accountLogHugeFromToTariffs[2]->tariffPeriod->id);
    }

    /**
     * Проверить, как смена тарифов конвертируется в "маленькие" диапазоны (с выравниванием по месяцам)
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogFromToTariffs1()
    {
        $dateTimeFirstDayOfCurMonth = (new DateTimeImmutable())->modify('first day of this month');
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 1])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(4, count($accountLogFromToTariffs));

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазоны 0 и 1)

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(1, $accountLogFromToTariffs[0]->tariffPeriod->id);

        // диапазон 1
        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[1]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 days')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[1]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(1, $accountLogFromToTariffs[1]->tariffPeriod->id);

        // 2го с 3го подключил месячный тариф
        // по этому тарифу с 3го до конца прошлого месяца (диапазон 2) и весь этот месяц (диапазон 3)

        // диапазон 2
        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[2]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[2]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(2, $accountLogFromToTariffs[2]->tariffPeriod->id);

        // диапазон 3
        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfCurMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[3]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfCurMonth->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[3]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(2, $accountLogFromToTariffs[3]->tariffPeriod->id);
    }

    /**
     * Проверить, как смена тарифов конвертируется в "маленькие" диапазоны (с выравниванием по месяцам)
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogFromToTariffs2()
    {
        $dateTimeFirstDayOfCurMonth = (new DateTimeImmutable())->modify('first day of this month');
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 2])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(3 + 12, count($accountLogFromToTariffs)); // 3 интервала + год (12 интервалов)

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазоны 0 и 1)

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(1, $accountLogFromToTariffs[0]->tariffPeriod->id);

        // диапазон 1
        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[1]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 days')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[1]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(1, $accountLogFromToTariffs[1]->tariffPeriod->id);

        // 2го сразу же подключил месячный тариф
        // по этому тарифу со 2го до конца прошлого месяца (диапазон 2)

        // диапазон 2
        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[2]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[2]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(2, $accountLogFromToTariffs[2]->tariffPeriod->id);

        // 4го сразу же подключил годовой тариф
        // по этому тарифу с 4го до конца этого месяца + еще 11 месяцев, то есть с 3 до конца прошлого месяца (диапазон 3), весь этот месяц (диапазон 4) и далее по месяцам

        // диапазон 3
        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+3 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[3]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[3]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(3, $accountLogFromToTariffs[3]->tariffPeriod->id);

        // диапазон 4
        $this->assertNotEmpty($accountLogFromToTariffs[4]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfCurMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[4]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[4]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfCurMonth->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[4]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(3, $accountLogFromToTariffs[4]->tariffPeriod->id);

        for ($i = 1; $i <= 10; $i++) { // неполный месяц уже проверили. А здесь проверяем полные 11 месяцев

            $dateTimeFirstDayOfCurMonth = $dateTimeFirstDayOfCurMonth->modify('first day of next month');

            // диапазон 4 + $i
            $this->assertNotEmpty($accountLogFromToTariffs[4 + $i]->dateFrom);
            $this->assertEquals(
                $dateTimeFirstDayOfCurMonth->format(DateTimeZoneHelper::DATE_FORMAT),
                $accountLogFromToTariffs[4 + $i]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
            );

            $this->assertNotEmpty($accountLogFromToTariffs[4 + $i]->dateTo);
            $this->assertEquals(
                $dateTimeFirstDayOfCurMonth->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
                $accountLogFromToTariffs[4 + $i]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
            );

            $this->assertEquals(3, $accountLogFromToTariffs[4 + $i]->tariffPeriod->id);

        }
    }

    /**
     * Проверить, как смена тарифов конвертируется в "маленькие" диапазоны (с выравниванием по месяцам) для тарифа без автопродления
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogWithoutAutoprolongation1()
    {
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 3])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(1, count($accountLogFromToTariffs));

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое число прошлого месяца, потому что должен закрыться автоматически на следующий день

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(4, $accountLogFromToTariffs[0]->tariffPeriod->id);
    }

    /**
     * Проверить, как смена тарифов конвертируется в "маленькие" диапазоны (с выравниванием по месяцам) для тарифа с одним автопродлением
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogWithoutAutoprolongation2()
    {
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 4])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(2, count($accountLogFromToTariffs));

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое число прошлого месяца, потому что должен закрыться автоматически на следующий день

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[0]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(5, $accountLogFromToTariffs[0]->tariffPeriod->id);

        // диапазон 1
        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[1]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogFromToTariffs[1]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(5, $accountLogFromToTariffs[1]->tariffPeriod->id);
    }

    /**
     * Проверить, что при пересечении диапазонов ресурсы не дублируются
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogResource()
    {
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');
        $dateTimeLastDayOfPrevMonth = (new DateTimeImmutable())->modify('last day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 5])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogHugeFromToTariffs = $accountTariff->getAccountLogHugeFromToTariffs();
        $this->assertEquals(2, count($accountLogHugeFromToTariffs));

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое число прошлого месяца

        // диапазон 0
        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[0]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateTo);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[0]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(1, $accountLogHugeFromToTariffs[0]->tariffPeriod->id);

        // 1го сразу же подключил месячный тариф
        // по этому тарифу абонентка с 1го до конца прошлого месяца и весь этот месяц, а ресурсы только за 1ое (и только 1 раз!)

        // диапазон 1
        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateFrom);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[1]->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateTo);
        $this->assertEquals(
            $dateTimeLastDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogHugeFromToTariffs[1]->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $this->assertEquals(2, $accountLogHugeFromToTariffs[1]->tariffPeriod->id);

        unset($accountLogHugeFromToTariffs);

        // 1й день прошлого месяца в абонентке участвует дважды, а в ресурсах только один раз (по каждому ресурсу, то есть всего 2 шт.)!
        $untarificatedPeriodss = $accountTariff->getUntarificatedResourcePeriods([[]]);

        // ресурсы за 1ое
        $dateYmd = $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT);
        if (!isset($untarificatedPeriodss[$dateYmd])) {
            $untarificatedPeriodss[$dateYmd] = [];
        }
        $this->assertEquals(2, count($untarificatedPeriodss[$dateYmd]));

        // ресурсы за 2ое
        $dateYmd = $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT);
        if (!isset($untarificatedPeriodss[$dateYmd])) {
            $untarificatedPeriodss[$dateYmd] = [];
        }
        $this->assertEquals(2, count($untarificatedPeriodss[$dateYmd]));

        // 1го со 3го выключил
        // ресурсов за другой день быть не должно
        $this->assertEquals(2, count($untarificatedPeriodss));
    }

}