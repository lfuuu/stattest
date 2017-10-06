<?php

namespace app\modules\uu\models\traits;

use app\classes\Html;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use Yii;

trait AccountTariffPackageTrait
{
    /**
     * @param int $accountTariffId
     * @throws \Exception
     */
    public static function actualizeDefaultPackages($accountTariffId)
    {
        $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
        $accountTariff->addOrCloseDefaultPackage();
    }

    /**
     * Если эта услуга активна - подключить базовый пакет. Если неактивна - закрыть все пакеты.
     *
     * @throws \Exception
     */
    public function addOrCloseDefaultPackage()
    {
        if (!in_array($this->service_type_id, ServiceType::$packages)) {
            return;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if ($this->tariff_period_id) {
                // подключить базовые пакеты
                $this->_addDefaultPackage();
            } else {
                // выключить все пакеты
                $this->_closeAllPackages();
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            throw $e;
        }

    }

    /**
     * Подключить базовые пакеты
     *
     * @throws \app\exceptions\ModelValidationException
     */
    private function _addDefaultPackage()
    {
        if ($this->_hasDefaultPackage()) {
            // хотя бы один базовый пакет уже подключен
            return;
        }

        /** @var TariffPeriod $tariffPeriod */
        $tariffPeriod = $this->tariffPeriod;
        $tariffStatuses = $this->_getPackageTariffStatuses();
        /** @var \app\models\Number $number */
        $number = $this->number;
        $defaultPackages = $tariffPeriod->tariff->findDefaultPackages($this->city_id, $number ? $number->ndc_type_id : null, $tariffStatuses);
        if (!$defaultPackages) {
            Yii::error('Не найден базовый пакет для услуги ' . $this->id, 'uu');
            return;
        }

        /** @var Tariff $defaultPackage */
        foreach ($defaultPackages as $defaultPackage) {
            $tariffPeriods = $defaultPackage->tariffPeriods;
            $tariffPeriod = reset($tariffPeriods);

            $accountTariffLogs = $this->accountTariffLogs;
            $accountTariffLog = end($accountTariffLogs); // базовый пакет должен быть подключен с самого начала (конца desc-списка)

            // подключить базовый пакет
            $accountTariffPackage = new AccountTariff();
            $accountTariffPackage->client_account_id = $this->client_account_id;
            $accountTariffPackage->service_type_id = ServiceType::ID_VOIP_PACKAGE_CALLS;
            $accountTariffPackage->region_id = $this->region_id;
            $accountTariffPackage->city_id = $this->city_id;
            $accountTariffPackage->prev_account_tariff_id = $this->id;
            if (!$accountTariffPackage->save()) {
                throw new ModelValidationException($accountTariffPackage);
            }

            $accountTariffPackageLog = new AccountTariffLog();
            $accountTariffPackageLog->account_tariff_id = $accountTariffPackage->id;
            $accountTariffPackageLog->tariff_period_id = $tariffPeriod->id;
            $accountTariffPackageLog->actual_from_utc = $accountTariffLog->actual_from_utc;
            $accountTariffPackageLog->insert_time = $accountTariffLog->actual_from_utc; // чтобы не было лишнего списания
            if (!$accountTariffPackageLog->save()) {
                throw new ModelValidationException($accountTariffPackageLog);
            }
        }
    }

    /**
     * @return int[]
     */
    private function _getPackageTariffStatuses()
    {
        $tarifStatuses = [];

        /** @var \app\models\Number $number */
        $number = $this->number;
        if (!$number) {
            // возможно, линия
            return $tarifStatuses;
        }

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $priceLevel = $clientAccount->price_level;
        $didGroup = $number->didGroup;
        $tarifStatuses[] = $didGroup->{'tariff_status_package' . $priceLevel}; // пакет с учетом уровня цен
        $tarifStatuses[] = $didGroup->tariff_status_beauty; // пакет за красивость

        return $tarifStatuses;
    }

    /**
     * Есть ли существующий базовый пакет.
     *
     * @return bool|null
     */
    private function _hasDefaultPackage()
    {
        /** @var AccountTariff[] $nextAccountTariffs */
        $nextAccountTariffs = $this->nextAccountTariffs;
        foreach ($nextAccountTariffs as $nextAccountTariff) {

            if (!$nextAccountTariff->tariff_period_id) {
                // закрытый
                continue;
            }

            if ($nextAccountTariff->tariffPeriod->tariff->is_default) {
                return true;
            }
        }

        return null;
    }

    /**
     * Закрыть все пакеты.
     *
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
     */
    private function _closeAllPackages()
    {
        /** @var AccountTariffLog[] $accountTariffLogs */
        $accountTariffLogs = $this->accountTariffLogs;
        $accountTariffLog = reset($accountTariffLogs); // пакет должен быть закрыт с даты закрытия самого тарифа (то есть начала desc-списка)
        if ($accountTariffLog->tariff_period_id) {
            Yii::error('Услуга ' . $this->id . ' закрыта, хотя не должна', 'uu');
            return;
        }

        // закрыть все пакеты
        /** @var AccountTariff[] $nextAccountTariffs */
        $nextAccountTariffs = $this->nextAccountTariffs;
        foreach ($nextAccountTariffs as $nextAccountTariff) {

            if (!$nextAccountTariff->tariff_period_id) {
                // уже закрыт
                continue;
            }

            /** @var AccountTariffLog[] $nextAccountTariffLogs */
            $nextAccountTariffLogs = $nextAccountTariff->accountTariffLogs;
            $nextAccountTariffLog = reset($nextAccountTariffLogs);  // последняя смена тарифа (в начале desc-списка)
            if ($nextAccountTariffLog->actual_from_utc > $accountTariffLog->actual_from_utc) {
                // что-то есть в будущем - отменить и закрыть
                if (!$nextAccountTariffLog->delete()) {
                    throw new ModelValidationException($nextAccountTariffLog);
                }
            } elseif ($nextAccountTariffLog->actual_from_utc == $accountTariffLog->actual_from_utc) {
                if (!$nextAccountTariffLog->tariff_period_id) {
                    // и так должно быть закрытие. Ничего не делаем
                    continue;
                }

                // что?! смена на другой тариф?! отменить и закрыть
                if (!$nextAccountTariffLog->delete()) {
                    throw new ModelValidationException($nextAccountTariffLog);
                }
            }

            // закрыть
            $nextAccountTariffLog = new AccountTariffLog();
            $nextAccountTariffLog->account_tariff_id = $nextAccountTariff->id;
            $nextAccountTariffLog->tariff_period_id = null;
            $nextAccountTariffLog->actual_from_utc = $accountTariffLog->actual_from_utc;
            $nextAccountTariffLog->insert_time = $accountTariffLog->actual_from_utc; // чтобы не было лишнего списания
            if (!$nextAccountTariffLog->save($runValidation = false)) { // пакет не может работать без основной услуги. Поэтому закрыть и точка, что бы там проверки не говорили "уже оплачено" и прочее!
                throw new ModelValidationException($nextAccountTariffLog);
            }
        }
    }

    /**
     * @return string
     */
    public function getNextAccountTariffsAsString()
    {
        if ($this->nextAccountTariffs) {
            $strings = array_map(
                function (AccountTariff $nextAccountTariff) {
                    return Html::a(
                        Html::encode($nextAccountTariff->getName(false)),
                        $nextAccountTariff->getUrl()
                    );
                },
                $this->nextAccountTariffs
            );
            return implode('<br />', $strings);
        }

        return Yii::t('common', '(not set)');
    }
}