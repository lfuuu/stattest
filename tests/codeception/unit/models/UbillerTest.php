<?php

namespace tests\codeception\unit\models;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\classes\AccountLogFromToResource;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Bill;
use app\modules\uu\models\Resource;
use app\modules\uu\tarificator\AutoCloseAccountTariffTarificator;
use app\modules\uu\tarificator\SetCurrentTariffTarificator;
use app\tests\codeception\fixtures\uu\AccountTariffFixture;
use app\tests\codeception\fixtures\uu\AccountTariffLogFixture;
use app\tests\codeception\fixtures\uu\AccountTariffResourceLogFixture;
use app\tests\codeception\fixtures\uu\TariffFixture;
use app\tests\codeception\fixtures\uu\TariffOrganizationFixture;
use app\tests\codeception\fixtures\uu\TariffPeriodFixture;
use app\tests\codeception\fixtures\uu\TariffResourceFixture;
use app\tests\codeception\fixtures\uu\TariffVoipCityFixture;
use app\tests\codeception\fixtures\uu\TariffVoipNdcTypeFixture;
use DateTimeImmutable;
use tests\codeception\unit\_TestCase;

/**
 * Class UbillingTest
 */
class UbillerTest extends _TestCase
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
        (new TariffOrganizationFixture)->load();
        (new TariffVoipCityFixture)->load();
        (new TariffVoipNdcTypeFixture)->load();
        (new TariffPeriodFixture)->load();
        (new TariffResourceFixture)->load();
        (new AccountTariffFixture)->load();
        (new AccountTariffLogFixture)->load();
        (new AccountTariffResourceLogFixture)->load();

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
        (new AccountTariffResourceLogFixture)->unload();
        (new AccountTariffLogFixture)->unload();
        (new AccountTariffFixture)->unload();
        (new TariffResourceFixture)->unload();
        (new TariffPeriodFixture)->unload();
        (new TariffOrganizationFixture)->unload();
        (new TariffVoipCityFixture)->unload();
        (new TariffVoipNdcTypeFixture)->unload();
        (new TariffFixture)->unload();
    }

    /**
     * Проверить, как смена тарифов конвертируется в "большие" диапазоны (по смене тарифов)
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogHugeFromToTariffs1()
    {
        AccountTariff::setIsFullTarification(true);

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
        AccountTariff::setIsFullTarification(true);

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
        AccountTariff::setIsFullTarification(true);

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
        AccountTariff::setIsFullTarification(true);

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
     * Проверить, что закрывается тариф без автопродления
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogWithoutAutoprolongation1()
    {
        AccountTariff::setIsFullTarification(true);

        $dateTimeYesterday = (new DateTimeImmutable())
            ->modify('-1 day')
            ->setTime(0, 0, 0);

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 3])->one();
        $this->assertNotEmpty($accountTariff);

        $accountTariffLogs = $accountTariff->accountTariffLogs;
        $this->assertEquals(2, count($accountTariffLogs));

        // вчера подключил дневной тариф
        // по этому тарифу только вчера, потому что должен закрыться автоматически сегодня

        // Вчера открыт
        $accountTariffLog = array_pop($accountTariffLogs);
        $this->assertEquals(
            $dateTimeYesterday->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountTariffLog->actual_from
        );
        $this->assertEquals(
            4,
            $accountTariffLog->tariff_period_id
        );

        // Сегодня закрыт
        $accountTariffLog = array_pop($accountTariffLogs);
        $this->assertEquals(
            $dateTimeYesterday->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountTariffLog->actual_from
        );
        $this->assertEquals(
            null,
            $accountTariffLog->tariff_period_id
        );
    }

    /**
     * Проверить, что закрывается тариф с одним автопродлением
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogWithoutAutoprolongation2()
    {
        AccountTariff::setIsFullTarification(true);

        $dateTimeYesterday = (new DateTimeImmutable())
            ->modify('-1 day')
            ->setTime(0, 0, 0);

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 4])->one();
        $this->assertNotEmpty($accountTariff);

        $accountTariffLogs = $accountTariff->accountTariffLogs;
        $this->assertEquals(2, count($accountTariffLogs));

        // вчера подключил дневной тариф
        // по этому тарифу только вчера и сегодня, потому что должен закрыться автоматически завтра

        // Вчера открыт
        $accountTariffLog = array_pop($accountTariffLogs);
        $this->assertEquals(
            $dateTimeYesterday->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountTariffLog->actual_from
        );
        $this->assertEquals(
            5,
            $accountTariffLog->tariff_period_id
        );

        // Завтра закрыт
        $accountTariffLog = array_pop($accountTariffLogs);
        $this->assertEquals(
            $dateTimeYesterday->modify('+2 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountTariffLog->actual_from
        );
        $this->assertEquals(
            null,
            $accountTariffLog->tariff_period_id
        );
    }

    /**
     * Проверить, что при пересечении диапазонов ресурсы-трафик не дублируются
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     */
    public function testAccountLogTrafficResource()
    {
        AccountTariff::setIsFullTarification(true);

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

        // 1й день прошлого месяца в абонентке участвует дважды, а в ресурсах только один раз. У телефонии только 1 ресурс-трафик (звонки), поэтому должна быть 1 шт.
        $untarificatedTrafficPeriodss = $accountTariff->getUntarificatedResourceTrafficPeriods();

        // ресурсы за 1ое
        $dateYmd = $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT);
        if (!isset($untarificatedTrafficPeriodss[$dateYmd])) {
            $untarificatedTrafficPeriodss[$dateYmd] = [];
        }
        $this->assertEquals(1, count($untarificatedTrafficPeriodss[$dateYmd]));

        // ресурсы за 2ое
        $dateYmd = $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT);
        if (!isset($untarificatedTrafficPeriodss[$dateYmd])) {
            $untarificatedTrafficPeriodss[$dateYmd] = [];
        }
        $this->assertEquals(1, count($untarificatedTrafficPeriodss[$dateYmd]));

        // 1го со 3го выключил
        // ресурсов за другой день быть не должно
        $this->assertEquals(2, count($untarificatedTrafficPeriodss));
    }

    /**
     * Проверить, правильность разбиения ресурсов-опций
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_resource_log.php
     */
    public function testAccountLogOptionResource()
    {
        // 1го сразу же подключил дневной тариф
        // 2го сразу же подключил месячный тариф
        // 4го сразу же подключил годовой тариф
        //
        // 1го с 1го увеличил до 3х линий
        // 1го с 1го увеличил линии до 6х
        // 1го с 3го уменьшил линии до 2х (до смены тарифа не должно учитываться, потом - должно)
        // с завтра увеличил до 10 линий (не должно учитываться)

        // по дневному тарифу:
        //      1-1: 1 линия (бесплатно)
        //      1-1: +2 линий
        //      1-1: +3 линий
        // по месячному тарифу:
        //      2-30: 1 линия (бесплатно)
        //      2-30: +5 линий
        // по годовому тарифу:
        //      4-30: 1 линия (бесплатно)
        //      4-30: +1 линии и еще 11 месяцев 1-30 числа
        //
        // всего должно быть 3 + 12 = 15 платных транзакций

        AccountTariff::setIsFullTarification(true);

        $dateTimeFirstDayOfThisMonth = (new DateTimeImmutable())->modify('first day of this month');

        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');
        $dateTimeLastDayOfPrevMonth = (new DateTimeImmutable())->modify('last day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 2])->one();
        $this->assertNotEmpty($accountTariff);

        /** @var AccountLogFromToResource[][] $untarificatedResourceOptionPeriodss */
        $untarificatedResourceOptionPeriodss = $accountTariff->getUntarificatedResourceOptionPeriods();

        // всего у ВАТС должен быть 6 ресурсов
        $this->assertEquals(7, count($untarificatedResourceOptionPeriodss));

        // но для тестирования ограничимся только "линиями"
        $this->assertTrue(isset($untarificatedResourceOptionPeriodss[Resource::ID_VPBX_ABONENT]));

        /** @var AccountLogFromToResource[] $untarificatedResourceOptionPeriods */
        $untarificatedResourceOptionPeriods = $untarificatedResourceOptionPeriodss[Resource::ID_VPBX_ABONENT];

        // должно быть 15 платных транзакций
        $this->assertEquals(15, count($untarificatedResourceOptionPeriods));

        // по дневному тарифу:
        //      1-1: 1 линия (бесплатно)
        //      1-1: +2 линий
        $untarificatedResourceOptionPeriod = array_shift($untarificatedResourceOptionPeriods);
        $this->assertEquals(2, $untarificatedResourceOptionPeriod->amountOverhead);
        $this->assertEquals(1 /* дневной */, $untarificatedResourceOptionPeriod->tariffPeriod->id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        // по дневному тарифу:
        //      1-1: +3 линий
        $untarificatedResourceOptionPeriod = array_shift($untarificatedResourceOptionPeriods);
        $this->assertEquals(3, $untarificatedResourceOptionPeriod->amountOverhead);
        $this->assertEquals(1 /* дневной */, $untarificatedResourceOptionPeriod->tariffPeriod->id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        // по месячному тарифу:
        //      2-30: 1 линия (бесплатно)
        //      2-30: +5 линий
        $untarificatedResourceOptionPeriod = array_shift($untarificatedResourceOptionPeriods);
        $this->assertEquals(5, $untarificatedResourceOptionPeriod->amountOverhead);
        $this->assertEquals(2 /* месячный */, $untarificatedResourceOptionPeriod->tariffPeriod->id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );
        $this->assertEquals(
            $dateTimeLastDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        // по годовому тарифу:
        //      4-30: 1 линия (бесплатно)
        //      4-30: +1 линии и еще 11 месяцев 1-30 числа
        $untarificatedResourceOptionPeriod = array_shift($untarificatedResourceOptionPeriods);
        $this->assertEquals(1, $untarificatedResourceOptionPeriod->amountOverhead);
        $this->assertEquals(3 /* годовой */, $untarificatedResourceOptionPeriod->tariffPeriod->id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+3 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
        );
        $this->assertEquals(
            $dateTimeLastDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $untarificatedResourceOptionPeriod->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        // и еще 11 месяцев 1-30 числа
        for ($i = 0; $i < 11; $i++) {
            $untarificatedResourceOptionPeriod = array_shift($untarificatedResourceOptionPeriods);
            $this->assertEquals(1, $untarificatedResourceOptionPeriod->amountOverhead);
            $this->assertEquals(3 /* годовой */, $untarificatedResourceOptionPeriod->tariffPeriod->id);
            $this->assertEquals(
                $dateTimeFirstDayOfThisMonth
                    ->modify('+' . $i . ' month')
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
                $untarificatedResourceOptionPeriod->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT)
            );
            $this->assertEquals(
                $dateTimeFirstDayOfThisMonth
                    ->modify('+' . $i . ' month')
                    ->modify('last day of this month')
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
                $untarificatedResourceOptionPeriod->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
            );
        }
    }

}