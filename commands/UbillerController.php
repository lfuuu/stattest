<?php

namespace app\commands;

use app\models\ClientAccount;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\Bill;
use app\modules\uu\tarificator\Tarificator;
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
            printf('Error. %s %s', $e->getMessage(), $e->getTraceAsString());
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
     *
     * @deprecated
     */
    private function _actionBillReConverter()
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
     *
     * @deprecated
     */
    private function _actionClear()
    {
        $this->_actionClearSetup();
        $this->_actionClearPeriod();
        $this->_actionClearResource();
        $this->_actionClearMin();
        $this->_actionClearEntry();
        $this->_actionClearBill();
    }

    /**
     * Удалить транзакции за подключение. 2 сек
     *
     * @deprecated
     */
    private function _actionClearSetup()
    {
        echo AccountLogSetup::deleteAll() . ' ' . PHP_EOL;
    }

    /**
     * Удалить транзакции абоненской платы. 2 сек
     *
     * @deprecated
     */
    private function _actionClearPeriod()
    {
        echo AccountLogPeriod::deleteAll() . ' ' . PHP_EOL;
    }

    /**
     * Удалить транзакции за ресурсы. 2 сек
     *
     * @deprecated
     */
    private function _actionClearResource()
    {
        echo AccountLogResource::deleteAll() . ' ' . PHP_EOL;
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
     * Удалить транзакции за минималку. 2 сек
     *
     * @deprecated
     */
    private function _actionClearMin()
    {
        echo AccountLogMin::deleteAll() . ' ' . PHP_EOL;
    }

    /**
     * Удалить проводки. 2 сек
     *
     * @deprecated
     */
    private function _actionClearEntry()
    {
        echo AccountLogSetup::updateAll(['account_entry_id' => null]) . ' ';
        echo AccountLogPeriod::updateAll(['account_entry_id' => null]) . ' ';
        echo AccountLogResource::updateAll(['account_entry_id' => null]) . ' ';
        echo AccountLogMin::updateAll(['account_entry_id' => null]) . ' ';
        echo AccountEntry::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить счета. 2 сек
     *
     * @deprecated
     */
    private function _actionClearBill()
    {
        // сначала надо удалить старые сконвертированные счета, иначе они останутся, потому что у них 'on delete set null'
        echo \app\models\Bill::deleteAll([
                'AND',
                ['biller_version' => ClientAccount::VERSION_BILLER_UNIVERSAL],
                ['IS NOT', 'uu_bill_id', null]
            ]) . ' ';

        echo AccountEntry::updateAll(['bill_id' => null]) . ' ';
        echo Bill::deleteAll() . ' ' . PHP_EOL;
    }
}
