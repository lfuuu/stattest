<?php
namespace app\commands;

use app\classes\uu\converter\AccountTariffConverter;
use app\classes\uu\converter\TariffConverter;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffResource;
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
        echo PHP_EOL . 'Тарифы. ' . date(DATE_ATOM) . PHP_EOL;

        $tariffConverter = new TariffConverter;

        if ($serviceTypeId) {
            $serviceTypeIds = [$serviceTypeId];
        } else {
            $serviceTypeIds = ServiceType::$ids;
        }

        foreach ($serviceTypeIds as $serviceTypeIdTmp) {
            try {
                $tariffConverter->convertByServiceTypeId($serviceTypeIdTmp);
            } catch (\Exception $e) {
                Yii::error('Ошибка конвертации');
                Yii::error($e);
                printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            }
        }

        echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
        return Controller::EXIT_CODE_NORMAL;
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
                $serviceTypeIds = [$serviceTypeId];
            } else {
                $serviceTypeIds = ServiceType::$ids;
            }

            foreach ($serviceTypeIds as $serviceTypeIdTmp) {
                try {
                    $tariffConverter->convertByServiceTypeId($serviceTypeIdTmp);
                } catch (\Exception $e) {
                    Yii::error('Ошибка конвертации');
                    Yii::error($e);
                    printf('%s %s', $e->getMessage(), $e->getTraceAsString());
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
     * Удалить кривые ресурсы тарифа
     * @return int
     */
    public function actionFixTariffResource()
    {
        $tariffTableName = Tariff::tableName();
        $resourceTableName = Resource::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $accountLogResourceTableName = AccountLogResource::tableName();

        $sql = <<<SQL
            CREATE TEMPORARY TABLE tariff_resource_tmp
            SELECT
                tariff_resource.id
            FROM
                {$tariffResourceTableName} tariff_resource,
                {$tariffTableName} tariff,
                {$resourceTableName} resource
            WHERE
                tariff_resource.tariff_id = tariff.id
                AND tariff_resource.resource_id = resource.id 
                AND tariff.service_type_id != resource.service_type_id
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        $sql = <<<SQL
            DELETE
                account_log_resource.*
            FROM
                {$accountLogResourceTableName} account_log_resource,
                tariff_resource_tmp
            WHERE
                account_log_resource.tariff_resource_id = tariff_resource_tmp.id
SQL;
        echo Yii::$app->db->createCommand($sql)->execute() . ' ';

        $sql = <<<SQL
            DELETE
                tariff_resource.*
            FROM
                {$tariffResourceTableName} tariff_resource,
                tariff_resource_tmp
            WHERE
                tariff_resource.id = tariff_resource_tmp.id
SQL;
        echo Yii::$app->db->createCommand($sql)->execute() . ' ';

        $sql = <<<SQL
            DROP TEMPORARY TABLE tariff_resource_tmp
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Синхронизировать пакеты в биллер
     * @return int
     */
    public function actionSyncAccountTariffLight()
    {
        $activeQuery = AccountLogPeriod::find()
            ->joinWith('accountTariff')
            ->where(['service_type_id' => ServiceType::ID_VOIP_PACKAGE])
            ->andWhere(['>=', 'account_tariff_id', AccountTariff::DELTA]);
        /** @var AccountLogPeriod $accountLogPeriod */
        foreach ($activeQuery->each() as $accountLogPeriod) {
            echo '. ';

            // эмулировать сохранение, чтобы сработали обработчики
            $accountLogPeriod->trigger(AccountLogPeriod::EVENT_BEFORE_UPDATE);
            $accountLogPeriod->trigger(AccountLogPeriod::EVENT_AFTER_UPDATE);
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Дефрагментировать AccountTariffLog
     * При конвертации логи тарифов удаляются и создаются заново. Так за несколько месяцев допустимые числа закончатся, конвертер перестанет работать.
     * Надо раз в месяц запускать дефрагментацию до тех пор, пока конвертер не выключим
     * @return int
     */
    public function actionFixAccountTariffLog()
    {
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $accountTariffDelta = AccountTariff::DELTA;

        $sql = <<<SQL
        CREATE TEMPORARY TABLE account_tariff_log_tmp (
            account_tariff_id int(11) NOT NULL,
            tariff_period_id int(11) DEFAULT NULL,
            insert_time datetime DEFAULT NULL,
            insert_user_id int(11) DEFAULT NULL,
            actual_from_utc datetime DEFAULT NULL
        )
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        $sql = <<<SQL
        INSERT INTO account_tariff_log_tmp
            (account_tariff_id, tariff_period_id, insert_time, insert_user_id, actual_from_utc)
        SELECT
            account_tariff_id, tariff_period_id, insert_time, insert_user_id, actual_from_utc
        FROM
            {$accountTariffLogTableName}
        WHERE
            account_tariff_id >= {$accountTariffDelta}
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        $sql = <<<SQL
        TRUNCATE TABLE {$accountTariffLogTableName}
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        $sql = <<<SQL
        INSERT INTO {$accountTariffLogTableName}
            (account_tariff_id, tariff_period_id, insert_time, insert_user_id, actual_from_utc)
        SELECT
            account_tariff_id, tariff_period_id, insert_time, insert_user_id, actual_from_utc
        FROM
            account_tariff_log_tmp
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        $sql = <<<SQL
        DROP TEMPORARY TABLE account_tariff_log_tmp
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        $this->actionAccountTariff();
    }
}
