<?php
namespace app\commands;

use app\classes\uu\converter\AccountTariffConverter;
use app\classes\uu\converter\TariffConverter;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\exceptions\api\internal\ExceptionValidationForm;
use app\models\TariffVoipPackage;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePricelist;
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
        $this->actionTariffVoipPackage();
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
     * Доконвертировать пакеты телефонии в ННП
     *
     * @return int
     */
    public function actionTariffVoipPackage()
    {
        try {
            echo PHP_EOL . 'Пакеты телефонии в ННП. ' . date(DATE_ATOM) . PHP_EOL;

            // услуг не так много (меньше 100) - можно их все прочитать в модели

            /** @var Package[] $nnpPackages */
            $nnpPackages = Package::find()->indexBy('tariff_id')->all();

            /** @var Tariff[] $tariffPackages */
            $tariffPackages = Tariff::find()->where(['service_type_id' => ServiceType::ID_VOIP_PACKAGE])->indexBy('id')->all();

            // при конвертации направления не совпадают, поэтому возьмем первое попавшееся, а менеджеры потом исправят вручную
            // исправить проще, чем создавать с нуля
            /** @var Destination $destination */
            $destination = Destination::find()->one();

            // добавить новые
            foreach ($tariffPackages as $tariffPackage) {
                if (isset($nnpPackages[$tariffPackage->id])) {

                    // уже есть
                    echo '. ';
                    $package = $nnpPackages[$tariffPackage->id];
                    unset($nnpPackages[$tariffPackage->id]);

                } else {

                    // создать новый
                    echo ', ';
                    $package = new Package();
                    $package->tariff_id = $tariffPackage->id;
                    if (!$package->save()) {
                        throw new ExceptionValidationForm($package);
                    }

                }

                // обновить минуты/прайс
                $nonUniversalId = $tariffPackage->id - Tariff::DELTA_VOIP_PACKAGE;
                $nonUniversalTariff = TariffVoipPackage::findOne(['id' => $nonUniversalId]);
                if (!$nonUniversalTariff) {
                    //throw new \LogicException('Не найден старый тариф для ' . $tariffPackage->id);
                    continue;
                }

                if ($nonUniversalTariff->minutes_count) {

                    // предоплаченные минуты
                    $packageMinutes = $package->packageMinutes;
                    if (count($packageMinutes) == 1) {
                        $packageMinute = reset($packageMinutes);
                        if (
                            $packageMinute->minute != $nonUniversalTariff->minutes_count ||
                            $packageMinute->destination_id != $nonUniversalTariff->destination_id
                        ) {
                            // Обновить
                            $packageMinute->minute = $nonUniversalTariff->minutes_count;
                            $packageMinute->destination_id = $destination->id; // $nonUniversalTariff->destination_id; // направления не совпадают!
                            if (!$packageMinute->save()) {
                                throw new ExceptionValidationForm($packageMinute);
                            }
                        }
                    } else {
                        // удалить и добавить заново
                        foreach ($packageMinutes as $packageMinute) {
                            $packageMinute->delete();
                        }
                        $packageMinute = new PackageMinute();
                        $packageMinute->tariff_id = $package->tariff_id;
                        $packageMinute->minute = $nonUniversalTariff->minutes_count;
                        $packageMinute->destination_id = $destination->id; // $nonUniversalTariff->destination_id; // направления не совпадают!
                        if (!$packageMinute->save()) {
                            throw new ExceptionValidationForm($packageMinute);
                        }
                    }

                    $packagePricelists = $package->packagePricelists;
                    foreach ($packagePricelists as $packagePricelist) {
                        $packagePricelist->delete();
                    }

                } else {

                    // прайс-лист
                    $packagePricelists = $package->packagePricelists;
                    if (count($packagePricelists) == 1) {
                        $packagePricelist = reset($packagePricelists);
                        if ($packagePricelist->pricelist_id != $nonUniversalTariff->pricelist_id) {
                            // Обновить
                            $packagePricelist->pricelist_id = $nonUniversalTariff->pricelist_id;
                            if (!$packagePricelist->save()) {
                                throw new ExceptionValidationForm($packagePricelist);
                            }
                        }
                    } else {
                        // удалить и добавить заново
                        foreach ($packagePricelists as $packagePricelist) {
                            $packagePricelist->delete();
                        }
                        $packagePricelist = new PackagePricelist();
                        $packagePricelist->tariff_id = $package->tariff_id;
                        $packagePricelist->pricelist_id = $nonUniversalTariff->pricelist_id;
                        if (!$packagePricelist->save()) {
                            throw new ExceptionValidationForm($packagePricelist);
                        }
                    }

                    $packageMinutes = $package->packageMinutes;
                    foreach ($packageMinutes as $packageMinute) {
                        $packageMinute->delete();
                    }
                }

                // фикс. цен не должно быть
                $packagePrices = $package->packagePrices;
                foreach ($packagePrices as $packagePrice) {
                    $packagePrice->delete();
                }

            }

            // удалить несуществующие
            foreach ($nnpPackages as $nnpPackage) {
                echo '# ';
                $nnpPackage->delete();
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
