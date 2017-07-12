<?php
namespace app\commands;

use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\Bill;
use app\modules\uu\tarificator\Tarificator;
use app\models\ClientAccount;
use Yii;
use yii\console\Controller;

/**
 * Универсальный тарификатор (тарификатор универсальных услуг)
 */
class UbillerController extends Controller
{

    /**
     * Создать транзакции, проводки, счета. hot 4 минуты / cold 3 часа
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

        // счета
        $this->actionBill();

        // Не списывать абонентку и минималку при финансовой блокировке
        $this->actionFreePeriodInFinanceBlock();

        // Конвертировать счета в старую бухгалтерию
        $this->actionBillConverter();

        // пересчитать realtimeBalance
        $this->actionRealtimeBalance();

        // Месячную финансовую блокировку заменить на постоянную
        $this->actionFinanceBlock();

        // Расчет технического кредита МГП
        $this->actionCreditMgp();

        return Controller::EXIT_CODE_NORMAL;
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
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return Controller::EXIT_CODE_ERROR;
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
     * Не списывать абонентку и минималку при финансовой блокировке. 1 секунда
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
     * Конвертировать все счета в старую бухгалтерию. 30 секунд
     */
    public function actionBillReConverter()
    {
        Bill::updateAll(['is_converted' => 0]);
        $this->actionBillConverter();
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
     * Расчет технического кредита МГП. 1 секунда
     */
    public function actionCreditMgp()
    {
        $this->_tarificate('CreditMgpTarificator', 'Расчет технического кредита МГП');
    }

    /**
     * Автоматически закрыть услугу по истечению тестового периода. 1 секунда
     */
    public function actionAutoCloseAccountTariff()
    {
        $this->_tarificate('AutoCloseAccountTariffTarificator', 'Автоматически закрыть услугу по истечению тестового периода');
    }

    /**
     * Удалить транзакции, проводки, счета. 10 сек
     */
    public function actionClear()
    {
        $this->actionClearSetup();
        $this->actionClearPeriod();
        $this->actionClearResource();
        $this->actionClearMin();
        $this->actionClearEntry();
        $this->actionClearBill();
    }

    /**
     * Удалить транзакции за подключение. 2 сек
     */
    public function actionClearSetup()
    {
        AccountLogSetup::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить транзакции абоненской платы. 2 сек
     */
    public function actionClearPeriod()
    {
        AccountLogPeriod::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить транзакции за ресурсы. 2 сек
     */
    public function actionClearResource()
    {
        AccountLogResource::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить транзакции за минималку. 2 сек
     */
    public function actionClearMin()
    {
        AccountLogMin::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить проводки. 2 сек
     */
    public function actionClearEntry()
    {
        AccountLogSetup::updateAll(['account_entry_id' => null]);
        AccountLogPeriod::updateAll(['account_entry_id' => null]);
        AccountLogResource::updateAll(['account_entry_id' => null]);
        AccountLogMin::updateAll(['account_entry_id' => null]);
        AccountEntry::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить счета. 2 сек
     */
    public function actionClearBill()
    {
        // сначала надо удалить старые сконвертированные счета, иначе они останутся, потому что у них 'on delete set null'
        \app\models\Bill::deleteAll([
            'AND',
            ['biller_version' => ClientAccount::VERSION_BILLER_UNIVERSAL],
            ['IS NOT', 'uu_bill_id', null]
        ]);

        AccountEntry::updateAll(['bill_id' => null]);
        Bill::deleteAll();
        echo '. ' . PHP_EOL;
    }
}
