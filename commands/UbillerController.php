<?php
namespace app\commands;

use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\Bill;
use app\classes\uu\tarificator\AccountEntryTarificator;
use app\classes\uu\tarificator\AccountLogPeriodTarificator;
use app\classes\uu\tarificator\AccountLogResourceTarificator;
use app\classes\uu\tarificator\AccountLogSetupTarificator;
use app\classes\uu\tarificator\BillTarificator;
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
        $this->actionSetup();
        $this->actionPeriod();
        $this->actionResource();
        $this->actionEntry();
        $this->actionBill();

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Создать транзакции за подключение. hot 40 секунд / cold 1 минута
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
     * Создать проводки. hot 1 секунда / cold 5 сек
     * На основе новых транзакций создать новые проводки или добавить в существующие
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
     * Создать счета. hot 1 секунда / cold 5 сек
     * На основе новых проводок создать новые счета или добавить в существующие
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
        AccountLogSetup::deleteAll();
        echo '. ';
        AccountLogPeriod::deleteAll();
        echo '. ';
        AccountLogResource::deleteAll();
        echo '. ';
        AccountEntry::deleteAll();
        echo '. ';
        Bill::deleteAll();
        echo '. ' . PHP_EOL;
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
}
