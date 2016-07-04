<?php
namespace app\commands;

use app\classes\uu\converter\AccountTariffConverter;
use app\classes\uu\converter\TariffConverter;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use Yii;
use yii\console\Controller;

/**
 * Конвертер старых тарифов/услуг в универсальные
 */
class UconverterController extends Controller
{

    /**
     * Доконвертировать тарифы и услуги
     * @return int
     */
    public function actionIndex()
    {
        $this->actionTariff();
        $this->actionAccountTariff();
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Доконвертировать тарифы
     *
     * @param int $serviceTypeId тип услуги. Если не указано, то все
     * @return int
     */
    public function actionTariff($serviceTypeId = null)
    {
        try {
            echo PHP_EOL . 'Тарифы. ' . date(DATE_ATOM) . PHP_EOL;

            $tariffConverter = new TariffConverter;

            if ($serviceTypeId) {
                $tariffConverter->convertByServiceTypeId($serviceTypeId);
            } else {
                foreach (ServiceType::$ids as $serviceTypeIdTmp) {
                    $tariffConverter->convertByServiceTypeId($serviceTypeIdTmp);
                }
            }

            echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            Yii::error('Ошибка конвертации');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Доконвертировать услуги
     * 
     * @param int $serviceTypeId тип услуги. Если не указано, то все
     * @return int
     */
    public function actionAccountTariff($serviceTypeId = null)
    {
        try {
            echo PHP_EOL . 'Услуги. ' . date(DATE_ATOM) . PHP_EOL;

            $tariffConverter = new AccountTariffConverter;

            if ($serviceTypeId) {
                $tariffConverter->convertByServiceTypeId($serviceTypeId);
            } else {
                foreach (ServiceType::$ids as $serviceTypeIdTmp) {
                    $tariffConverter->convertByServiceTypeId($serviceTypeIdTmp);
                }
            }

            echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            Yii::error('Ошибка конвертации');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Удалить все универсальные тарифы и услуги
     */
    public function actionClear()
    {
        $this->actionClearAccountTariff();
        $this->actionClearTariff();
    }

    /**
     * Удалить все универсальные тарифы
     */
    public function actionClearTariff()
    {
        Tariff::deleteAll();
        echo '. ' . PHP_EOL;
    }

    /**
     * Удалить все универсальные услуги
     */
    public function actionClearAccountTariff()
    {
        AccountTariff::deleteAll();
        echo '. ' . PHP_EOL;
    }
}
