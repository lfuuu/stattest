<?php

namespace tests\codeception\unit\models;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Bill;
use app\tests\codeception\fixtures\uu\AccountTariffFixture;
use app\tests\codeception\fixtures\uu\AccountTariffLogFixture;
use app\tests\codeception\fixtures\uu\TariffFixture;
use app\tests\codeception\fixtures\uu\TariffPeriodFixture;
use DateTimeImmutable;
use yii\codeception\TestCase;

/**
 * Class UbillingTest
 * @package tests\codeception\unit\models
 * @link http://codeception.com/docs/modules/Asserts
 */
class UbillingTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->unload();
        $this->load();
    }

    protected function tearDown()
    {
        $this->unload();
        parent::tearDown();
    }

    protected function load()
    {
        (new TariffFixture)->load();
        (new TariffPeriodFixture)->load();
        (new AccountTariffFixture)->load();
        (new AccountTariffLogFixture)->load();
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
        $accountTariff = AccountTariff::find()->where(['id' => 1])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogHugeFromToTariffs = $accountTariff->getAccountLogHugeFromToTariffs();
        $this->assertEquals(count($accountLogHugeFromToTariffs), 2);

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазон 0)

        // диапазон 0
        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateFrom);
        $this->assertEquals($accountLogHugeFromToTariffs[0]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateTo);
        $this->assertEquals($accountLogHugeFromToTariffs[0]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertEquals($accountLogHugeFromToTariffs[0]->tariffPeriod->id, 1);

        // 2го с 3го подключил месячный тариф
        // по этому тарифу с 3го до конца прошлого месяца и весь этот месяц (диапазон 1)

        // диапазон 1
        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateFrom);
        $this->assertEquals($accountLogHugeFromToTariffs[1]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format('Y-m-d'));

        $this->assertEmpty($accountLogHugeFromToTariffs[1]->dateTo);

        $this->assertEquals($accountLogHugeFromToTariffs[1]->tariffPeriod->id, 2);
    }

    /**
     * Проверить, как смена тарифов конвертируется в "большие" диапазоны (по смене тарифов)
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogHugeFromToTariffs2()
    {
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => 2])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogHugeFromToTariffs = $accountTariff->getAccountLogHugeFromToTariffs();
        $this->assertEquals(count($accountLogHugeFromToTariffs), 3);

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазон 0)

        // диапазон 0
        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateFrom);
        $this->assertEquals($accountLogHugeFromToTariffs[0]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogHugeFromToTariffs[0]->dateTo);
        $this->assertEquals($accountLogHugeFromToTariffs[0]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertEquals($accountLogHugeFromToTariffs[0]->tariffPeriod->id, 1);

        // 2го сразу же подключил месячный тариф
        // по этому тарифу со 2го до конца прошлого месяца (диапазон 1)

        // диапазон 1
        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateFrom);
        $this->assertEquals($accountLogHugeFromToTariffs[1]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertNotEmpty($accountLogHugeFromToTariffs[1]->dateTo);
        $this->assertEquals($accountLogHugeFromToTariffs[1]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format('Y-m-d'));

        $this->assertEquals($accountLogHugeFromToTariffs[1]->tariffPeriod->id, 2);

        // 4го сразу же подключил годовой тариф
        // по этому тарифу с 4го до конца этого года (диапазон 2)

        // диапазон 2
        $this->assertNotEmpty($accountLogHugeFromToTariffs[2]->dateFrom);
        $this->assertEquals($accountLogHugeFromToTariffs[2]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+3 days')->format('Y-m-d'));

        $this->assertEmpty($accountLogHugeFromToTariffs[2]->dateTo);

        $this->assertEquals($accountLogHugeFromToTariffs[2]->tariffPeriod->id, 3);
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
        $accountTariff = AccountTariff::find()->where(['id' => 1])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(count($accountLogFromToTariffs), 4);

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазоны 0 и 1)

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[0]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[0]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[0]->tariffPeriod->id, 1);

        // диапазон 1
        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[1]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[1]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 days')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[1]->tariffPeriod->id, 1);

        // 2го с 3го подключил месячный тариф
        // по этому тарифу с 3го до конца прошлого месяца (диапазон 2) и весь этот месяц (диапазон 3)

        // диапазон 2
        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[2]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[2]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[2]->tariffPeriod->id, 2);

        // диапазон 3
        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[3]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfCurMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[3]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfCurMonth->modify('last day of this month')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[3]->tariffPeriod->id, 2);
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
        $accountTariff = AccountTariff::find()->where(['id' => 2])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $nMonthsUpToTheYearEnd = 12 - $dateTimeFirstDayOfCurMonth->format('n'); // сколько еще месяцев (кроме текущего) осталось до конца календарного года
        $this->assertEquals(count($accountLogFromToTariffs), 5 + $nMonthsUpToTheYearEnd);

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое и 2ое число прошлого месяца (диапазоны 0 и 1)

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[0]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[0]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[0]->tariffPeriod->id, 1);

        // диапазон 1
        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[1]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[1]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 days')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[1]->tariffPeriod->id, 1);

        // 2го сразу же подключил месячный тариф
        // по этому тарифу со 2го до конца прошлого месяца (диапазон 2)

        // диапазон 2
        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[2]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[2]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[2]->tariffPeriod->id, 2);

        // 4го сразу же подключил годовой тариф
        // по этому тарифу с 4го до конца этого года, то есть с 3 до конца прошлого месяца (диапазон 3), весь этот месяц (диапазон 4) и далее по месяцам до конца календарного года

        // диапазон 3
        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[3]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+3 day')->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[3]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[3]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('last day of this month')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[3]->tariffPeriod->id, 3);

        // диапазон 4
        $this->assertNotEmpty($accountLogFromToTariffs[4]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[4]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfCurMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[4]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[4]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfCurMonth->modify('last day of this month')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[4]->tariffPeriod->id, 3);

        for ($i = 1; $i <= $nMonthsUpToTheYearEnd; $i++) {

            $dateTimeFirstDayOfCurMonth = $dateTimeFirstDayOfCurMonth->modify('first day of next month');

            // диапазон 4 + $i
            $this->assertNotEmpty($accountLogFromToTariffs[4 + $i]->dateFrom);
            $this->assertEquals($accountLogFromToTariffs[4 + $i]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfCurMonth->format('Y-m-d'));

            $this->assertNotEmpty($accountLogFromToTariffs[4 + $i]->dateTo);
            $this->assertEquals($accountLogFromToTariffs[4 + $i]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfCurMonth->modify('last day of this month')->format('Y-m-d'));

            $this->assertEquals($accountLogFromToTariffs[4 + $i]->tariffPeriod->id, 3);

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
        $accountTariff = AccountTariff::find()->where(['id' => 3])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(count($accountLogFromToTariffs), 2);

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое число прошлого месяца, потому что должен закрыться автоматически на следующий день

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[0]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[0]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[0]->tariffPeriod->id, 4);

        // диапазон 1
        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[1]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertEmpty($accountLogFromToTariffs[1]->dateTo);

        $this->assertEmpty($accountLogFromToTariffs[1]->tariffPeriod->id);
    }

    /**
     * Проверить, как смена тарифов конвертируется в "маленькие" диапазоны (с выравниванием по месяцам) для тарифа с одним автопродлением
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogWithoutAutoprolongation2()
    {
        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => 4])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(count($accountLogFromToTariffs), 3);

        // 1го сразу же подключил дневной тариф
        // по этому тарифу только 1ое число прошлого месяца, потому что должен закрыться автоматически на следующий день

        // диапазон 0
        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[0]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[0]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[0]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[0]->tariffPeriod->id, 5);

        // диапазон 1
        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[1]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertNotEmpty($accountLogFromToTariffs[1]->dateTo);
        $this->assertEquals($accountLogFromToTariffs[1]->dateTo->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format('Y-m-d'));

        $this->assertEquals($accountLogFromToTariffs[1]->tariffPeriod->id, 5);

        // диапазон 2
        $this->assertNotEmpty($accountLogFromToTariffs[2]->dateFrom);
        $this->assertEquals($accountLogFromToTariffs[2]->dateFrom->format('Y-m-d'), $dateTimeFirstDayOfPrevMonth->modify('+2 days')->format('Y-m-d'));

        $this->assertEmpty($accountLogFromToTariffs[2]->dateTo);

        $this->assertEmpty($accountLogFromToTariffs[2]->tariffPeriod->id);
    }


}