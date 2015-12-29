<?php
namespace app\commands;

use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use Yii;
use yii\console\Controller;

/**
 * Универсальный тарификатор (тарификатор универсальных услуг)
 */
class UbillerController extends Controller
{

    /**
     * Рассчитать все
     * @return int
     */
    public function actionIndex()
    {
        echo 'Универсальный тарификатор запущен' . PHP_EOL;

        $this->actionSetup();
        $this->actionPeriod();
        $this->actionResource();

        echo PHP_EOL . PHP_EOL . 'Универсальный тарификатор закончил работу' . PHP_EOL;
        return 0;
    }

    /**
     * Рассчитать плату за подключение
     */
    public function actionSetup()
    {
        try {
            echo PHP_EOL . 'Плата за подключение: ' . PHP_EOL;
            AccountLogSetup::tarificateAll();
        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Очистить плату за подключение
     */
    public function actionClearSetup()
    {
        AccountLogSetup::deleteAll();
    }

    /**
     * Рассчитать абоненскую плату
     */
    public function actionPeriod()
    {
        try {
            echo PHP_EOL . 'Абонентская плата: ' . PHP_EOL;
            AccountLogPeriod::tarificateAll();
        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Очистить абоненскую плату
     */
    public function actionClearPeriod()
    {
        AccountLogPeriod::deleteAll();
    }

    /**
     * Рассчитать плату за ресурсы
     */
    public function actionResource()
    {
        try {
            echo PHP_EOL . 'Плата за ресурсы: ' . PHP_EOL;
            AccountLogResource::tarificateAll();
        } catch (\Exception $e) {
            Yii::error('Ошибка универсального тарификатора');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Очистить плату за ресурсы
     */
    public function actionClearResource()
    {
        AccountLogResource::deleteAll();
    }
}
