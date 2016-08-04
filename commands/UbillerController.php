<?php
namespace app\commands;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogMin;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\Bill;
use app\classes\uu\tarificator\AccountEntryTarificator;
use app\classes\uu\tarificator\AccountLogMinTarificator;
use app\classes\uu\tarificator\AccountLogPeriodTarificator;
use app\classes\uu\tarificator\AccountLogResourceTarificator;
use app\classes\uu\tarificator\AccountLogSetupTarificator;
use app\classes\uu\tarificator\BillTarificator;
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
        // транзакции
        $this->actionSetup();
        $this->actionPeriod();
        $this->actionResource();
        $this->actionMin();

        // проводки
        $this->actionEntry();

        // счета
        $this->actionBill();

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Создать транзакции за подключение. hot 40 секунд / cold 1 минута
     * Предоплата
     * Каждое подключение - отдельная транзакция
     * Смена тарифа для не-телефонии считается тоже подключением
     */
    public function actionSetup()
    {
        try {

            echo PHP_EOL . 'Плата за подключение. ' . date(DATE_ATOM) . PHP_EOL;
            (new AccountLogSetupTarificator)->tarificateAll();
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
     * Создать транзакции абоненской платы. hot 40 секунд / cold 13 минут
     * Предоплата
     * Абонентская плата берется с выравниванием по периоду списания, то есть до конца текущего периода (месяца, квартала, года)
     * Если получилось меньше периода списания - пропорционально кол-ву дней
     * Разбить по календарным месяцам, каждый меся записать отдельной транзакцией
     */
    public function actionPeriod()
    {
        try {

            echo PHP_EOL . 'Абонентская плата. ' . date(DATE_ATOM) . PHP_EOL;
            (new AccountLogPeriodTarificator)->tarificateAll();
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
     * Создать транзакции за ресурсы. hot 3 минуты / cold 3 часа
     * Постоплата посуточно
     * По каждому ресурсу за каждые сутки - отдельная транзакция
     */
    public function actionResource()
    {
        try {

            echo PHP_EOL . 'Плата за ресурсы. ' . date(DATE_ATOM) . PHP_EOL;
            (new AccountLogResourceTarificator)->tarificateAll();
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
     * Создать транзакции минималки за ресурсы.
     * Предоплата помесячно
     */
    public function actionMin()
    {
        try {

            echo PHP_EOL . 'Минималка за ресурсы. ' . date(DATE_ATOM) . PHP_EOL;
            (new AccountLogMinTarificator)->tarificateAll();
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
     * Создать проводки. hot 1 секунда / cold 5 сек
     * На основе новых транзакций создать новые проводки или добавить в существующие
     * Проводка - группировка всех транзакций по календарному месяцу по каждой услуге
     */
    public function actionEntry()
    {
        try {

            echo PHP_EOL . 'Проводки. ' . date(DATE_ATOM) . PHP_EOL;
            (new AccountEntryTarificator)->tarificateAll();
            echo date(DATE_ATOM) . PHP_EOL;
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Создать счета-фактуры. hot 1 секунда / cold 5 сек
     * На основе новых проводок создать новые счета-фактуры или добавить в существующие
     * Счет-фактура - группировка всех проводок по календарному месяцу. Сколько услуг у клиента - столько проводок будет в счете
     * Счет-фактура у каждого клиента всегда только 1 за месяц
     */
    public function actionBill()
    {
        try {

            echo PHP_EOL . 'Счета. ' . date(DATE_ATOM) . PHP_EOL;
            (new BillTarificator)->tarificateAll();
            echo date(DATE_ATOM) . PHP_EOL;
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return Controller::EXIT_CODE_ERROR;
        }
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

        foreach($clientAccounts as $clientAccount) {
            echo PHP_EOL . 'client: ' . $clientAccount->id;

            $transaction = Yii::$app->getDb()->beginTransaction();
            stdBill::dao()->transferUniversalBillsToBills($clientAccount);
            $transaction->commit();
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
