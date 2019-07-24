<?php

namespace app\commands;

use app\modules\uu\behaviors\AccountTariffBiller;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\tarificator\AccountEntryTarificator;
use app\modules\uu\tarificator\AccountLogMinTarificator;
use app\modules\uu\tarificator\AccountLogPeriodTarificator;
use app\modules\uu\tarificator\AccountLogResourceTarificator;
use app\modules\uu\tarificator\AccountLogSetupTarificator;
use app\modules\uu\tarificator\AutoCloseAccountTariffTarificator;
use app\modules\uu\tarificator\BillConverterTarificator;
use app\modules\uu\tarificator\BillTarificator;
use app\modules\uu\tarificator\FinanceBlockTarificator;
use app\modules\uu\tarificator\FreePeriodInFinanceBlockTarificator;
use app\modules\uu\tarificator\RealtimeBalanceTarificator;
use app\modules\uu\tarificator\SetCurrentTariffTarificator;
use app\modules\uu\tarificator\SyncResourceTarificator;
use app\modules\uu\tarificator\Tarificator;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Универсальный тарификатор (тарификатор универсальных услуг)
 */
class UbillerController extends Controller
{

    /**
     * Создать транзакции/проводки/счета за вчера и сегодня. hot 4 минуты / cold 3 часа
     *
     * @return int
     */
    public function actionIndex()
    {
        // Обновить AccountTariff.TariffPeriod на основе AccountTariffLog
        // Проверить баланс при смене тарифа. Если денег не хватает - отложить на день
        // обязательно это вызывать до транзакций (чтобы они правильно посчитали)
        $this->actionSetCurrentTariff();

        // Автоматически закрыть услугу по истечению тестового периода
        // Обязательно после actionSetCurrentTariff (чтобы правильно учесть тариф) и до транзакций (чтобы они правильно посчитали)
        $this->actionAutoCloseAccountTariff();

        // Еще раз обновить AccountTariff.TariffPeriod на основе AccountTariffLog
        // Второй раз вызываем после actionAutoCloseAccountTariff, чтобы сразу (фактически) закрыть УУ, которые выше решили закрыть (теоретически)
        $this->actionSetCurrentTariff();

        // Отправить измененные ресурсы на платформу и другим поставщикам услуг
        // Обязательно после actionSetCurrentTariff, чтобы измененный тариф сам синхронизировал некоторые ресурсы
        $this->actionSyncResource();

        // транзакции
        $this->actionSetup();
        $this->actionPeriod();
        $this->actionResource();
        $this->actionMin();

        // проводки
        $this->actionEntry();

		// Не списывать абонентку и минималку (обнулять транзакции) за ВТОРОЙ и последующие периоды при финансовой блокировке
		// Должно идти после actionEntry (чтобы проводки уже были), но до actionBill (чтобы проводки правильно учлись в счете)
		$this->actionFreePeriodInFinanceBlock();

        // счета
        $this->actionBill();

        // Конвертировать счета в старую бухгалтерию
        $this->actionBillConverter();

        // пересчитать realtimeBalance
        $this->actionRealtimeBalance();

        // Месячную финансовую блокировку заменить на постоянную
        // $this->actionFinanceBlock();

        return ExitCode::OK;
    }

    /**
     * Создать транзакции/проводки/счета за этот и прошлый месяц. hot 4 минуты / cold 3 часа
     *
     * @return int
     */
    public function actionFull()
    {
        AccountTariff::setIsFullTarification(true);
        return $this->actionIndex();
    }

    /**
     * Тарифицировать, вызвав нужный класс
     *
     * @param string $className
     * @return int
     */
    protected function executeRater($className)
    {
        try {
            /** @var Tarificator $rater */
            $rater = (new $className($isEcho = true));
            echo PHP_EOL . $rater->getDescription() . '. ' . date(DATE_ATOM) . PHP_EOL;

            $rater->tarificate();
            echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
            return ExitCode::OK;

        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора. Класс ' . substr(strrchr($className, "\\"), 1));
            Yii::error($e);
            printf('Error. %s %s', $e->getMessage(), $e->getTraceAsString());
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Создать транзакции за подключение. hot 40 секунд / cold 1 минута
     * Предоплата
     * Каждое подключение - отдельная транзакция
     * Смена тарифа для не-телефонии считается тоже подключением
     */
    public function actionSetup()
    {
        $this->executeRater(AccountLogSetupTarificator::class);
    }

    /**
     * Создать транзакции абоненской платы. hot 40 секунд / cold 13 минут
     * Предоплата
     * Абонентская плата берется с выравниванием по периоду списания, то есть до конца текущего периода (месяца, квартала, года)
     * Если получилось меньше периода списания - пропорционально кол-ву дней
     * Разбить по календарным месяцам, каждый месяц записать отдельной транзакцией
     */
    public function actionPeriod()
    {
        $this->executeRater(AccountLogPeriodTarificator::class);
    }

    /**
     * Создать транзакции за ресурсы. hot 3 минуты / cold 3 часа
     * Постоплата посуточно
     * По каждому ресурсу за каждые сутки - отдельная транзакция
     */
    public function actionResource()
    {
        $this->executeRater(AccountLogResourceTarificator::class);
    }

    /**
     * Создать транзакции минималки за ресурсы. 5 сек.
     * Предоплата по аналогии с абоненткой
     */
    public function actionMin()
    {
        $this->executeRater(AccountLogMinTarificator::class);
    }

    /**
     * Создать проводки. hot 1 секунда / cold 5 сек
     * На основе новых транзакций создать новые проводки или добавить в существующие
     */
    public function actionEntry()
    {
        $this->executeRater(AccountEntryTarificator::class);
    }

    /**
     * Создать счета. hot 1 секунда / cold 5 сек
     * На основе новых проводок создать новые счета-фактуры или добавить в существующие
     */
    public function actionBill()
    {
        $this->executeRater(BillTarificator::class);
    }

    /**
     * Не списывать абонентку и минималку (обнулять транзакции) за ВТОРОЙ и последующие периоды при финансовой блокировке. 1 секунда
	 * Должно идти после actionEntry (чтобы проводки уже были), но до actionBill (чтобы проводки правильно учлись в счете)
     */
    public function actionFreePeriodInFinanceBlock()
    {
        $this->executeRater(FreePeriodInFinanceBlockTarificator::class);
    }

    /**
     * Конвертировать новые счета в старую бухгалтерию. 3 секунды
     */
    public function actionBillConverter()
    {
        $this->executeRater(BillConverterTarificator::class);
    }

    /**
     * Обновить AccountTariff.TariffPeriod на основе AccountTariffLog. 10 секунд
     * Проверить баланс при смене тарифа. Если денег не хватает - отложить на день.
     * Обязательно это вызывать до транзакций (чтобы они правильно посчитали)
     */
    public function actionSetCurrentTariff()
    {
        $this->executeRater(SetCurrentTariffTarificator::class);
    }

    /**
     * Отправить измененные ресурсы на платформу и другим поставщикам услуг
     */
    public function actionSyncResource()
    {
        $this->executeRater(SyncResourceTarificator::class);
    }

    /**
     * Пересчитать realtimeBalance. 1 секунда
     */
    public function actionRealtimeBalance()
    {
        $this->executeRater(RealtimeBalanceTarificator::class);
    }

    /**
     * Месячную финансовую блокировку заменить на постоянную. 1 секунда
     */
    public function actionFinanceBlock()
    {
        $this->executeRater(FinanceBlockTarificator::class);
    }

    /**
     * Автоматически закрыть услугу по истечению тестового периода. 1 секунда
     */
    public function actionAutoCloseAccountTariff()
    {
        $this->executeRater(AutoCloseAccountTariffTarificator::class);
    }

    /**
     * Удалить транзакции за ресурсы-звонки в предыдущем месяце. 5 сек
     *
     * @throws \yii\db\Exception
     */
    public function actionClearResourceCallsPrevMonth()
    {
        echo AccountLogResource::clearCalls('first day of previous month', 'last day of previous month') . ' ' . PHP_EOL;
    }

    /**
     * Удалить транзакции за ресурсы-звонки в этом месяце. 1-3 сек
     *
     * @throws \yii\db\Exception
     */
    public function actionClearResourceCallsThisMonth()
    {
        echo AccountLogResource::clearCalls('first day of this month', 'last day of this month') . ' ' . PHP_EOL;
    }

    /**
     * Запустить пересчет проводок и конвертировать счета
     */
    public function actionFixBills()
    {
        // проводки
        $this->actionEntry();

        // Не списывать абонентку и минималку (обнулять транзакции) за ВТОРОЙ и последующие периоды при финансовой блокировке
        // Должно идти после actionEntry (чтобы проводки уже были), но до actionBill (чтобы проводки правильно учлись в счете)
        $this->actionFreePeriodInFinanceBlock();

        // счета
        $this->actionBill();

        // Конвертировать счета в старую бухгалтерию
        $this->actionBillConverter();

        // пересчитать realtimeBalance
        $this->actionRealtimeBalance();
    }

    /**
     * Пересчёт конкретной услуги конкретного клиента
     *
     * @param int|null $clientId
     * @param int|null $accountTariffId
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionCalcAccountTariff($clientId = null, $accountTariffId = null)
    {
        if (is_null($clientId) || is_null($accountTariffId)) {
            echo 'Please provide data with client_account_id and account_tariff_id.' . PHP_EOL;
        } else {
            $params = [
                'client_account_id' => $clientId,
                'account_tariff_id' => $accountTariffId,
            ];

            echo sprintf('Билинговать услугу #%s у клиента #%s', $accountTariffId, $clientId) . PHP_EOL;
            AccountTariffBiller::recalc($params);
            echo PHP_EOL . 'Done!' . PHP_EOL;
        }
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 4391334, 'message' => 'Универсальный биллер'];
    }
}
