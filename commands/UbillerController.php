<?php
namespace app\commands;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\Bill;
use app\classes\uu\tarificator\TarificatorI;
use app\models\Bill as stdBill;
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
     * @return int
     */
    public function actionIndex()
    {
        // проверить баланс при смене тарифа. Если денег не хватает - отложить на день
        // обязательно это вызывать до транзакций (чтобы они правильно посчитали)
        $this->actionChangeTariff();

        // Обновить AccountTariff.TariffPeriod на основе AccountTariffLog
        $this->actionSetCurrentTariff();

        // Автоматически закрыть услугу по истечению тестового периода
        // Обязательно после actionSetCurrentTariff (чтобы правильно учесть тариф) и до транзакций (чтобы они правильно посчитали)
        $this->actionAutoCloseAccountTariff();

        // транзакции
        $this->actionSetup();
        $this->actionPeriod();
        $this->actionResource();
        $this->actionMin();

        // Не списывать абонентку и минималку при финансовой блокировке
        // Ибо аккаунт все равно не может пользоваться услугами
        // Обязательно после транзакций и до проводок
        $this->actionFreePeriodInFinanceBlock();

        // проводки
        $this->actionEntry();

        // счета
        $this->actionBill();

        // пересчитать realtimeBalance
        $this->actionRealtimeBalance();

        // Месячную финансовую блокировку заменить на постоянную
        $this->actionFinanceBlock();

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Тарифицировать, вызвав нужный класс
     * @param string $className
     * @param string $name
     */
    protected function _tarificate($className, $name)
    {
        try {
            echo PHP_EOL . $name . '. ' . date(DATE_ATOM) . PHP_EOL;
            $className = '\\app\\classes\\uu\\tarificator\\' . $className;
            /** @var TarificatorI $tarificator */
            $tarificator = (new $className);
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
     * Проводка - группировка всех транзакций по календарному месяцу по каждой услуге
     */
    public function actionEntry()
    {
        $this->_tarificate('AccountEntryTarificator', 'Проводки');
    }

    /**
     * Создать счета-фактуры. hot 1 секунда / cold 5 сек
     * На основе новых проводок создать новые счета-фактуры или добавить в существующие
     * Счет-фактура - группировка всех проводок по календарному месяцу. Сколько услуг у клиента - столько проводок будет в счете
     * Счет-фактура у каждого клиента всегда только 1 за месяц
     */
    public function actionBill()
    {
        $this->_tarificate('BillTarificator', 'Счета');
    }


    /**
     * Проверить баланс при смене тарифа. 1 секунда
     * Если не хватает денег при смене тарифа - откладывать смену по +1 день, пока деньги не появятся, тогда списать.
     */
    public function actionChangeTariff()
    {
        $this->_tarificate('ChangeTariffTarificator', 'Проверить баланс при смене тарифа');
    }

    /**
     * Обновить AccountTariff.TariffPeriod на основе AccountTariffLog. 10 секунд
     */
    public function actionSetCurrentTariff()
    {
        $this->_tarificate('SetCurrentTariffTarificator', 'Обновить AccountTariff.TariffPeriod');
    }

    /**
     * пересчитать realtimeBalance. 1 секунда
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
     * Не списывать абонентку и минималку при финансовой блокировке. 1 секунда
     * Ибо аккаунт все равно не может пользоваться услугами
     */
    public function actionFreePeriodInFinanceBlock()
    {
        $this->_tarificate('FreePeriodInFinanceBlockTarificator', 'Не списывать абонентку и минималку при финансовой блокировке');
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
        AccountEntry::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить счета. 2 сек
     */
    public function actionClearBill()
    {
        Bill::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * перенос проводок в неуниверсальные счета
     * @param int|null $clientAccountId
     */
    public function actionTransferBills($clientAccountId = null)
    {
        $clientAccountsQuery = ClientAccount::find()->where(['is_active' => 1]);
        if ($clientAccountId) {
            $clientAccountsQuery->andWhere(['id' => $clientAccountId]);
        }
        $clientAccounts = $clientAccountsQuery->all();

        foreach ($clientAccounts as $clientAccount) {
            echo PHP_EOL . 'client: ' . $clientAccount->id;

            stdBill::dao()->transferUniversalBillsToBills($clientAccount);
        }

        echo PHP_EOL . 'done.';
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Удаление счетов, сделанных из проводок универсального биллера
     */
    public function actionCleanTransferedBills()
    {
        stdBill::deleteAll(['biller_version' => ClientAccount::VERSION_BILLER_UNIVERSAL]);

        return Controller::EXIT_CODE_NORMAL;
    }
}
