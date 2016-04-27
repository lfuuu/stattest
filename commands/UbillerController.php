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
     * Создать транзакции, проводки, счета
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
     * Создать транзакции за подключение
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
     * Создать транзакции абоненской платы
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
     * Создать транзакции за ресурсы
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
     * Создать проводки
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
     * Создать счета
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
     * Удалить транзакции, проводки, счета
     */
    public function actionClear()
    {
        AccountLogSetup::deleteAll();
        AccountLogPeriod::deleteAll();
        AccountLogResource::deleteAll();
        AccountEntry::deleteAll();
        Bill::deleteAll();
    }

    /**
     * Удалить транзакции за подключение
     */
    public function actionClearSetup()
    {
        AccountLogSetup::deleteAll();
    }

    /**
     * Удалить транзакции абоненской платы
     */
    public function actionClearPeriod()
    {
        AccountLogPeriod::deleteAll();
    }

    /**
     * Удалить транзакции за ресурсы
     */
    public function actionClearResource()
    {
        AccountLogResource::deleteAll();
    }

    /**
     * Удалить проводки
     */
    public function actionClearEntry()
    {
        AccountEntry::deleteAll();
    }

    /**
     * Удалить счета
     */
    public function actionClearBill()
    {
        Bill::deleteAll();
    }
}
