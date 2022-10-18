<?php

namespace tests\codeception\unit\models;

use app\classes\HandlerLogger;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\OperationType;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Bill;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipCountry;
use app\modules\uu\models\TariffVoipNdcType;
use app\modules\uu\tarificator\AccountEntryTarificator;
use app\modules\uu\tarificator\AccountLogPeriodTarificator;
use app\modules\uu\tarificator\AccountLogResourceTarificator;
use app\modules\uu\tarificator\AutoCloseAccountTariffTarificator;
use app\modules\uu\tarificator\BillConverterTarificator;
use app\modules\uu\tarificator\BillTarificator;
use app\modules\uu\tarificator\RealtimeBalanceTarificator;
use app\modules\uu\tarificator\SetCurrentTariffTarificator;
use app\tests\codeception\fixtures\uu\AccountTariffFixture;
use app\tests\codeception\fixtures\uu\AccountTariffLogFixture;
use app\tests\codeception\fixtures\uu\AccountTariffResourceLogFixture;
use app\tests\codeception\fixtures\uu\TariffCountryFixture;
use app\tests\codeception\fixtures\uu\TariffFixture;
use app\tests\codeception\fixtures\uu\TariffOrganizationFixture;
use app\tests\codeception\fixtures\uu\TariffPeriodFixture;
use app\tests\codeception\fixtures\uu\TariffResourceFixture;
use app\tests\codeception\fixtures\uu\TariffVoipCityFixture;
use app\tests\codeception\fixtures\uu\TariffVoipCountryFixture;
use app\tests\codeception\fixtures\uu\TariffVoipNdcTypeFixture;
use DateTimeImmutable;
use DateTimeZone;
use tests\codeception\unit\_TestCase;

/**
 * Class UbillingTest
 */
class UbillerTest extends _TestCase
{
    /**
     * @throws \Exception
     * @throws \Throwable
     */

    public $isMonthTransition = false;

    protected function setUp()
    {
        // отслеживаем переход месяца
        if (in_array(date('d'), [1, 2])) {
            $this->isMonthTransition = true;
        }

        parent::setUp();

        self::unloadUu();
        self::loadUu();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public static function loadUu()
    {
        (new TariffFixture)->load();
        (new TariffOrganizationFixture)->load();
        (new TariffVoipCityFixture)->load();
        (new TariffVoipNdcTypeFixture)->load();
        (new TariffPeriodFixture)->load();
        (new TariffResourceFixture)->load();
        (new TariffCountryFixture)->load();
        (new TariffVoipCountryFixture)->load();
        (new AccountTariffFixture)->load();
        (new AccountTariffLogFixture)->load();
        (new AccountTariffResourceLogFixture)->load();

        AccountTariff::setIsFullTarification(true);
        (new SetCurrentTariffTarificator)->tarificate(null, false);
        (new AutoCloseAccountTariffTarificator)->tarificate(null, false);

        HandlerLogger::me()->clear();
    }

    public static function unloadUu()
    {
        AccountLogSetup::deleteAll();
        AccountLogPeriod::deleteAll();
        AccountLogResource::deleteAll();
        AccountLogMin::deleteAll();
        AccountEntry::deleteAll();
        Bill::deleteAll();
        AccountTariffResourceLog::deleteAll();
        AccountTariffLog::deleteAll();
        AccountTariff::deleteAll();
        TariffResource::deleteAll();
        TariffPeriod::deleteAll();
        TariffVoipCity::deleteAll();
        TariffOrganization::deleteAll();
        TariffVoipNdcType::deleteAll();
        TariffCountry::deleteAll();
        TariffVoipCountry::deleteAll();
        Tariff::deleteAll();
        EventQueue::deleteAll();
    }

    /**
     * Проверить, как смена тарифов конвертируется в "большие" диапазоны (по смене тарифов)
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
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
     * Проверить, что закрывается тариф без автопродления
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_log.php
     * @throws \Exception
     */
    public function testAccountLogWithoutAutoprolongation1()
    {
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
     * @throws \Exception
     */
    public function testAccountLogWithoutAutoprolongation2()
    {
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
     *
     * @throws \Throwable
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\StaleObjectException
     */
    public function testAccountLogTrafficResource()
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
     * Ресурсы-трафик звонки оригинация
     *
     * @throws \Throwable
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\StaleObjectException
     */
    public function testAccountLogTrafficPriceResource()
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 8])->one();
        $this->assertNotEmpty($accountTariff);

        // ***
        // билингуем
        (new AccountLogResourceTarificator)->tarificateAccountTariffTraffic($accountTariff);
        (new AccountEntryTarificator)->tarificate($accountTariff->id);
        (new BillTarificator)->tarificate($accountTariff->id);

        // за 3 дня
        $accountLogResources = $accountTariff->accountLogResources;
        $this->assertEquals(3, count($accountLogResources));

        // транзакции на том же тариф-периоде
        $accountLogResource = array_shift($accountLogResources);
        $this->assertEquals($accountTariff->tariff_period_id, $accountLogResource->tariff_period_id);

        // операция у проводки - доход
        $accountEntry = $accountLogResource->accountEntry;
        $this->assertEquals(OperationType::ID_PRICE, $accountEntry->operation_type_id);

        // операция у счёта - доход
        $bill = $accountEntry->bill;
        $this->assertEquals(OperationType::ID_PRICE, $bill->operation_type_id);

        // всего проводок
        $accountEntries = $accountTariff->accountEntries;
        $this->assertEquals($this->isMonthTransition ? 7 : 4, count($accountEntries));

        // проводки другие (недоходные)
        $accountEntries = array_filter($accountEntries, function (AccountEntry $accountEntry) {
            return $accountEntry->tariffResource->resource_id != ResourceModel::ID_TRUNK_PACKAGE_ORIG_CALLS;
        });
        $this->assertEquals($this->isMonthTransition ? 5 : 3, count($accountEntries));

        // операция у другой проводки - доход, счёт тот же
        $accountEntry = array_shift($accountEntries);
        $this->assertEquals(OperationType::ID_PRICE, $accountEntry->operation_type_id);
        $this->assertTrue($accountEntry->price >= 0);
        $this->assertTrue($accountEntry->bill_id === $bill->id);

        // ***
        // создание старого счёта
        (new BillConverterTarificator)->transferBill($bill);
        (new RealtimeBalanceTarificator)->tarificate($accountTariff->client_account_id, $accountTariff->id);

        // Универсальный счёт сконвертирован
        $this->assertEquals($bill->is_converted, 1);

        // операция у нового "старого" счёта - расход
        $newBill = $bill->newBill;
        $this->assertEquals(OperationType::ID_PRICE, $newBill->operation_type_id);
        $this->assertTrue($newBill->sum > 0);
        $this->assertNotEmpty($newBill->lines);

        // ***
        // создание с/ф
        $newBill->generateInvoices();

        // Кол-во с/ф
        $invoices = $newBill->invoices;
        $this->assertEquals(2, count($invoices));
    }

    /**
     * Ресурсы-трафик звонки терминация
     *
     * @throws \Throwable
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\StaleObjectException
     */
    public function testAccountLogTrafficCostResource()
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 10])->one();
        $this->assertNotEmpty($accountTariff);

        // ***
        // билингуем Звонки (терминация)
        (new AccountLogResourceTarificator)->tarificateAccountTariffTraffic($accountTariff);
        (new AccountEntryTarificator)->tarificate($accountTariff->id);
        (new BillTarificator)->tarificate($accountTariff->id);

        // за 3 дня
        $accountLogResources = $accountTariff->accountLogResources;
        $this->assertEquals(3, count($accountLogResources));

        // транзакции на том же тариф-периоде
        $accountLogResource = array_shift($accountLogResources);
        $this->assertEquals($accountTariff->tariff_period_id, $accountLogResource->tariff_period_id);

        // операция у проводки - расход
        $priceForAccountEntryCostCanBeZero = false;
        $accountEntry = $accountLogResource->accountEntry;

        $this->assertEquals(OperationType::ID_COST, $accountEntry->operation_type_id);
        if ($accountEntry->price == 0) {
            // для нулевой расходной проводки проверим все ее транзакции
            // помечаем, что цена может быть нулевой
            foreach ($accountEntry->accountLogResources as $accountLogResource) {
                $this->assertEquals(
                    OperationType::ID_COST,
                    ResourceModel::$operationTypesMap[$accountLogResource->tariffResource->resource->id] ?? ''
                );
                $this->assertTrue($accountEntry->price <= 0);
            }

            $priceForAccountEntryCostCanBeZero = true;
        } else {
            $this->assertTrue($accountEntry->price < 0);
        }

        // операция у счёта - расход
        $billCost = $accountEntry->bill;
        $this->assertEquals(OperationType::ID_COST, $billCost->operation_type_id);
        if ($priceForAccountEntryCostCanBeZero) {
            $this->assertTrue($billCost->price <= 0);
        } else {
            $this->assertTrue($billCost->price < 0);
        }

        // всего проводок
        $accountEntries = $accountTariff->accountEntries;
        $this->assertEquals($this->isMonthTransition ? 7 : 4, count($accountEntries));

        // проводки другие (нерасходные)
        $accountEntries = array_filter($accountEntries, function (AccountEntry $accountEntry) {
            return $accountEntry->operation_type_id != OperationType::ID_COST;
        });
        $this->assertEquals($this->isMonthTransition ? 5 : 3, count($accountEntries));

        // операция у другой проводки - доход
        $accountEntry = array_shift($accountEntries);
        $this->assertEquals(OperationType::ID_PRICE, $accountEntry->operation_type_id);
        $this->assertTrue($accountEntry->price >= 0);

        // операция у другого счёта другой проводки - доход, счета разные
        $billPrice = $accountEntry->bill;
        $this->assertEquals(OperationType::ID_PRICE, $billPrice->operation_type_id);
        $this->assertTrue($billPrice->price >= 0);
        $this->assertFalse($billPrice->id === $billCost->id);

        // ***
        // создание старого счёта
        (new BillConverterTarificator)->transferBill($billCost);
        (new RealtimeBalanceTarificator)->tarificate($accountTariff->client_account_id, $accountTariff->id);

        // Универсальный счёт сконвертирован
        $this->assertTrue($billCost->is_converted === 1);

        // для ненулевых - проверяем создание нового "старого" счета (nispd.newbill)
        if ($billCost->price != 0) {
            // операция у нового "старого" счёта - расход, строк нет
            $newBill = $billCost->newBill;
            $this->assertEquals(OperationType::ID_COST, $newBill->operation_type_id);
            $this->assertTrue($newBill->sum < 0);
            $this->assertNotEmpty($newBill->lines);

            // ***
            // создание с/ф
            $newBill->generateInvoices();

            // нет с/ф
            $invoices = $newBill->invoices;
            $this->assertEmpty($invoices);
        }
    }

    /**
     * Проверить, правильность разбиения ресурсов-опций
     * см. комментарии в tests/codeception/fixtures/uu/data/uu_account_tariff_resource_log.php
     * @throws \Exception
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

        $dateTimeFirstDayOfThisMonth = (new DateTimeImmutable())->modify('first day of this month');

        $dateTimeFirstDayOfPrevMonth = (new DateTimeImmutable())->modify('first day of previous month');
        $dateTimeLastDayOfPrevMonth = (new DateTimeImmutable())->modify('last day of previous month');

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 2])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogResources = $accountTariff->accountLogResources;

        $this->assertEquals(99, count($accountLogResources));

        // У ВАТС 7 ресурсов. Для тестирования ограничимся только "линиями" (15 транзакций)
        /** @var AccountLogResource[] $accountLogResources */
        $accountLogResources = array_filter($accountLogResources, function (AccountLogResource $accountLogResource) {
            return $accountLogResource->tariffResource->resource_id == ResourceModel::ID_VPBX_ABONENT;
        });
        $this->assertEquals(15, count($accountLogResources));

        // по дневному тарифу:
        //      1-1: 1 линия (бесплатно)
        //      1-1: +2 линий
        $accountLogResource = array_shift($accountLogResources);
        $this->assertEquals(2, $accountLogResource->amount_overhead);
        $this->assertEquals(1 /* дневной */, $accountLogResource->tariff_period_id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_from
        );
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_to
        );

        // по дневному тарифу:
        //      1-1: +3 линий
        $accountLogResource = array_shift($accountLogResources);
        $this->assertEquals(3, $accountLogResource->amount_overhead);
        $this->assertEquals(1 /* дневной */, $accountLogResource->tariff_period_id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_from
        );
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_to
        );

        // по месячному тарифу:
        //      2-30: 1 линия (бесплатно)
        //      2-30: +5 линий
        $accountLogResource = array_shift($accountLogResources);
        $this->assertEquals(5, $accountLogResource->amount_overhead);
        $this->assertEquals(2 /* месячный */, $accountLogResource->tariff_period_id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_from
        );
        $this->assertEquals(
            $dateTimeLastDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_to
        );

        // по годовому тарифу:
        //      4-30: 1 линия (бесплатно)
        //      4-30: +1 линии и еще 11 месяцев 1-30 числа
        $accountLogResource = array_shift($accountLogResources);
        $this->assertEquals(1, $accountLogResource->amount_overhead);
        $this->assertEquals(3 /* годовой */, $accountLogResource->tariff_period_id);
        $this->assertEquals(
            $dateTimeFirstDayOfPrevMonth->modify('+3 day')->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_from
        );
        $this->assertEquals(
            $dateTimeLastDayOfPrevMonth->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogResource->date_to
        );

        // и еще 11 месяцев 1-30 числа
        for ($i = 0; $i < 11; $i++) {
            $accountLogResource = array_shift($accountLogResources);
            $this->assertEquals(1, $accountLogResource->amount_overhead);
            $this->assertEquals(3 /* годовой */, $accountLogResource->tariff_period_id);
            $this->assertEquals(
                $dateTimeFirstDayOfThisMonth
                    ->modify('+' . $i . ' month')
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
                $accountLogResource->date_from
            );
            $this->assertEquals(
                $dateTimeFirstDayOfThisMonth
                    ->modify('+' . $i . ' month')
                    ->modify('last day of this month')
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
                $accountLogResource->date_to
            );
        }
    }

    /**
     * Проверить разовый пакет интернета, сгорающий через 2 месяца
     * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=25887484
     * @throws \Exception
     */
    public function testBurnInternet()
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where(['id' => AccountTariff::DELTA + 6])->one();
        $this->assertNotEmpty($accountTariff);

        $accountLogFromToTariffs = $accountTariff->getAccountLogFromToTariffs();
        $this->assertEquals(2, count($accountLogFromToTariffs));

        $startDatiTime = (new DateTimeImmutable('now', $accountTariff->clientAccount->getTimezone()))
            ->modify('first day of previous month')
            ->modify('+14 day')
            ->setTime(0, 0, 0);

        $accountLogPeriod = (new AccountLogPeriodTarificator())
            ->getAccountLogPeriod($accountTariff, reset($accountLogFromToTariffs));

        $this->assertEquals(
            $startDatiTime->format(DateTimeZoneHelper::DATE_FORMAT),
            $accountLogPeriod->date_from
        );

        // С точки зрения бухгалтерии транзакция-проводка-счет идет только за первый месяц с коэффициентом 1, а во все последующие месяцы нет никаких транзакций-проводок.
        // Потому что непонятно, как делить стоимость и трафик между месяцами. Да и по логике он не делится и не дается по месяцам, а предоставляется весь и сразу.
        // А уж за какой период пользователь его потратит - это уже совсем другое, бухгалтерию это не касается.
        $this->assertEquals(514, $accountLogPeriod->price);
        $this->assertEquals(1, $accountLogPeriod->coefficient);

        $this->assertTrue($accountLogPeriod->save());

        /** @var EventQueue[] $eventQueues */
        $eventQueues = EventQueue::find()
            ->where([
                'account_tariff_id' => $accountTariff->prev_account_tariff_id,
                'event' => \app\modules\mtt\Module::EVENT_CLEAR_INTERNET,
            ])
            ->all();

        $this->assertEquals(1, count($eventQueues));
        $this->assertEquals(
            $startDatiTime
                ->modify('+1 day')
                ->modify('+2 month')
                ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
            reset($eventQueues)->next_start
        );
    }
}