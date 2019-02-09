<?php

namespace app\commands;

use app\models\billing\CallsRaw;
use app\models\billing\Locks;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
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
     * @param string $name
     * @return int
     */
    private function _tarificate($className, $name)
    {
        try {
            echo PHP_EOL . $name . '. ' . date(DATE_ATOM) . PHP_EOL;
            $className = '\\app\\modules\\uu\\tarificator\\' . $className;
            /** @var Tarificator $tarificator */
            $tarificator = (new $className($isEcho = true));
            $tarificator->tarificate();
            echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
            return ExitCode::OK;

        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора');
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
        $this->_tarificate('AccountLogSetupTarificator', 'Плата за подключение');
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
        $this->_tarificate('AccountLogPeriodTarificator', 'Абонентская плата');
    }

    /**
     * Создать транзакции за ресурсы. hot 3 минуты / cold 3 часа
     * Постоплата посуточно
     * По каждому ресурсу за каждые сутки - отдельная транзакция
     */
    public function actionResource()
    {
        $this->_tarificate('AccountLogResourceTarificator', 'Плата за ресурсы');
    }

    /**
     * Создать транзакции минималки за ресурсы. 5 сек.
     * Предоплата по аналогии с абоненткой
     */
    public function actionMin()
    {
        $this->_tarificate('AccountLogMinTarificator', 'Минималка за ресурсы');
    }

    /**
     * Создать проводки. hot 1 секунда / cold 5 сек
     * На основе новых транзакций создать новые проводки или добавить в существующие
     */
    public function actionEntry()
    {
        $this->_tarificate('AccountEntryTarificator', 'Проводки');
    }

    /**
     * Создать счета. hot 1 секунда / cold 5 сек
     * На основе новых проводок создать новые счета-фактуры или добавить в существующие
     */
    public function actionBill()
    {
        $this->_tarificate('BillTarificator', 'Счета');
    }

    /**
     * Не списывать абонентку и минималку (обнулять транзакции) за ВТОРОЙ и последующие периоды при финансовой блокировке. 1 секунда
	 * Должно идти после actionEntry (чтобы проводки уже были), но до actionBill (чтобы проводки правильно учлись в счете)
     */
    public function actionFreePeriodInFinanceBlock()
    {
        $this->_tarificate('FreePeriodInFinanceBlockTarificator', 'Не списывать абонентку и минималку при финансовой блокировке');
    }

    /**
     * Конвертировать новые счета в старую бухгалтерию. 3 секунды
     */
    public function actionBillConverter()
    {
        $this->_tarificate('BillConverterTarificator', 'Конвертировать счета в старую бухгалтерию');
    }

    /**
     * Обновить AccountTariff.TariffPeriod на основе AccountTariffLog. 10 секунд
     * Проверить баланс при смене тарифа. Если денег не хватает - отложить на день.
     * Обязательно это вызывать до транзакций (чтобы они правильно посчитали)
     */
    public function actionSetCurrentTariff()
    {
        $this->_tarificate('SetCurrentTariffTarificator', 'Обновить AccountTariff.TariffPeriod');
    }

    /**
     * Отправить измененные ресурсы на платформу и другим поставщикам услуг
     */
    public function actionSyncResource()
    {
        $this->_tarificate('SyncResourceTarificator', 'Отправить измененные ресурсы на платформу');
    }

    /**
     * Пересчитать realtimeBalance. 1 секунда
     */
    public function actionRealtimeBalance()
    {
        $this->_tarificate('RealtimeBalanceTarificator', 'RealtimeBalance');
    }

    /**
     * Месячную финансовую блокировку заменить на постоянную. 1 секунда
     */
    public function actionFinanceBlock()
    {
        $this->_tarificate('FinanceBlockTarificator', 'Месячную финансовую блокировку заменить на постоянную');
    }

    /**
     * Автоматически закрыть услугу по истечению тестового периода. 1 секунда
     */
    public function actionAutoCloseAccountTariff()
    {
        $this->_tarificate('AutoCloseAccountTariffTarificator', 'Автоматически закрыть услугу по истечению тестового периода');
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

    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 4391334, 'message' => 'Универсальный биллер'];
    }
}
