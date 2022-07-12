<?php

namespace app\modules\uu\models\traits;

use app\classes\HandlerLogger;
use app\classes\Html;
use app\exceptions\ModelValidationException;
use app\helpers\Semaphore;
use app\models\ClientAccount;
use app\models\DidGroup;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffStatus;
use Yii;
use yii\base\InvalidParamException;

trait AccountTariffPackageTrait
{
    /**
     * @param int $accountTariffId
     * @throws \Exception
     */
    public static function actualizeDefaultPackages($accountTariffId)
    {
        $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
        if (!$accountTariff) {
            throw new InvalidParamException('Услуга не найдена: ' . $accountTariffId);
        }

        if (!Semaphore::me()->acquire(Semaphore::ID_UU_CALCULATOR, false)) {
            throw new \LogicException('calculator busy, try restart later');
        }

        try {
            $accountTariff->addOrCloseDefaultPackage();
        } catch (\Exception $e) {
            Semaphore::me()->release(Semaphore::ID_UU_CALCULATOR);
            throw $e;
        }
        Semaphore::me()->release(Semaphore::ID_UU_CALCULATOR);

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

            if ($this->isActive()) {
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
            HandlerLogger::me()->add($e->getMessage());
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
        $packageType = ServiceType::$serviceToPackage[$this->service_type_id] ?? null;

        if (
            !$packageType
            || $this->_hasDefaultPackage()
        ) {
            // хотя бы один базовый пакет уже подключен
            // или услуга без пекетов
            return;
        }

        /** @var TariffPeriod $tariffPeriod */
        $tariffPeriod = $this->getNotNullTariffPeriod();
        $tariffStatuses = $this->getPackageTariffStatuses();
        /** @var \app\models\Number $number */
        $number = $this->number;

        $countryId = $this->clientAccount->getUuCountryId();

        /*
        if ($number) {
            $countryId = $number->country_code;
        } elseif ($this->city_id) {
            $countryId = $this->city->country_id;
        } elseif ($this->region_id) {
            $countryId = $this->region->country_id;
        } else {
            $countryId = null;
        }
        */

        if ($number && $number->ndc_type_id == NdcType::ID_MOBILE) {
            $packageType = [$packageType, ServiceType::ID_VOIP_PACKAGE_SMS, ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY];
        }

        $defaultPackages = $tariffPeriod->tariff->findDefaultPackages(
            $countryId,
            $this->city_id,
            $number ? $number->country_code : null,
            $number ? $number->ndc_type_id : null,
            $tariffPeriod->tariff->is_include_vat,
            $tariffStatuses,
            $packageType,
            $this->clientAccount->contract->organization_id
        );

        if (!$defaultPackages) {
            Yii::error('Не найден базовый пакет для услуги ' . $this->id, 'uu');
            return;
        }

        /** @var Tariff $defaultPackage */
        foreach ($defaultPackages as $defaultPackage) {
            $this->_addPackage($defaultPackage);
        }
    }

    /**
     * @return int[]
     */
    public function getPackageTariffStatuses()
    {
        if ($this->service_type_id != ServiceType::ID_VOIP) {
            return [TariffStatus::ID_PUBLIC];
        }

        $tariffStatuses = [];

        /** @var \app\models\Number $number */
        $number = $this->number;
        if (!$number) {
            // возможно, линия
            return $tariffStatuses;
        }

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $priceLevel = $clientAccount->price_level;
        $didGroup = $number->didGroup;
        $tariffStatuses[] = $didGroup->getTariffStatusPackage($priceLevel); // пакет с учетом уровня цен
        $clientAccount->uu_tariff_status_id && $tariffStatuses[] = $clientAccount->uu_tariff_status_id; // пакет персонально клиенту
        if ($priceLevel >= DidGroup::MIN_PRICE_LEVEL_FOR_BEAUTY) {
            // только для ОТТ (см. ClientAccount::getPriceLevels)
            $tariffStatuses[] = $didGroup->tariff_status_beauty; // пакет за красивость
        }

        return $tariffStatuses;
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

            if (!$nextAccountTariff->isActive()) {
                // закрытый
                continue;
            }

            /** @var TariffPeriod $tariffPeriod */
            $tariffPeriod = $nextAccountTariff->getNotNullTariffPeriod();
            if ($tariffPeriod->tariff->is_default) {
                return true;
            }
        }

        return null;
    }

    /**
     * Закрыть все пакеты.
     *
     * @throws \yii\db\StaleObjectException
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
            $this->_closePackage($nextAccountTariff, $accountTariffLog->actual_from_utc);
        }
    }


    /**
     * @param Tariff $tariff
     * @throws ModelValidationException
     */
    private function _addPackage(Tariff $tariff)
    {
        $tariffPeriods = $tariff->tariffPeriods;
        $tariffPeriod = reset($tariffPeriods);

        $accountTariffLogs = $this->accountTariffLogs;
        $accountTariffLog = end($accountTariffLogs); // базовый пакет должен быть подключен с самого начала (конца desc-списка)

        // подключить базовый пакет
        $accountTariffPackage = new AccountTariff();
        $accountTariffPackage->client_account_id = $this->client_account_id;
        $accountTariffPackage->service_type_id = $tariff->service_type_id;
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

    /**
     * @param AccountTariff $nextAccountTariff
     * @param string $actual_from_utc
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function _closePackage(AccountTariff $nextAccountTariff, $actual_from_utc)
    {
        if (!$nextAccountTariff->tariff_period_id) {
            // уже закрыт
            return;
        }

        /** @var AccountTariffLog[] $nextAccountTariffLogs */
        $nextAccountTariffLogs = $nextAccountTariff->accountTariffLogs;
        $nextAccountTariffLog = reset($nextAccountTariffLogs);  // последняя смена тарифа (в начале desc-списка)
        if ($nextAccountTariffLog->actual_from_utc > $actual_from_utc) {
            // что-то есть в будущем - отменить и закрыть
            if (!$nextAccountTariffLog->delete()) {
                throw new ModelValidationException($nextAccountTariffLog);
            }
        } elseif ($nextAccountTariffLog->actual_from_utc == $actual_from_utc) {
            if (!$nextAccountTariffLog->tariff_period_id) {
                // и так должно быть закрытие. Ничего не делаем
                return;
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
        $nextAccountTariffLog->actual_from_utc = $actual_from_utc;
        $nextAccountTariffLog->insert_time = $actual_from_utc; // чтобы не было лишнего списания
        if (!$nextAccountTariffLog->save($runValidation = false)) { // пакет не может работать без основной услуги. Поэтому закрыть и точка, что бы там проверки не говорили "уже оплачено" и прочее!
            throw new ModelValidationException($nextAccountTariffLog);
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
                    $string = Html::a(
                        Html::encode($nextAccountTariff->getName(false)),
                        $nextAccountTariff->getUrl()
                    );

                    if (!$nextAccountTariff->tariff_period_id) {
                        $string = Html::tag('strike', $string);
                    }

                    return $string;
                },
                $this->nextAccountTariffs
            );
            return implode('<br />', $strings);
        }

        return Yii::t('common', '(not set)');
    }

    /**
     * Вернуть кол-во потраченных минут по пакету минут
     *
     * @return array [[i_nnp_package_minute_id, i_used_seconds]]
     * @throws \yii\db\Exception
     */
    public function getMinuteStatistic()
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        $accountLogPeriod = $this->accountLogPeriodLast;

        return $accountLogPeriod ? $accountLogPeriod->getMinutesSummaryAsArray() : [];
    }

    public function getInternetStatistic()
    {
        $internetStatistic = [];

        static $internetDataCache = [];
        $did = $this->prevAccountTariff->voip_number;

        if (!$did) {
            return $internetStatistic;
        }

        if (!isset($internetDataCache[$did])) {
            $statInternets = \app\models\billing\StatsAccount::getStatInternet($did);

            $alts = AccountTariffLight::find()
                ->where(['id' => array_map(function ($v) {
                    return $v['account_tariff_light_id'];
                }, $statInternets)])
                ->select('account_package_id')->indexBy('id')->column();

            foreach ($statInternets as $statInternet) {
                if (
                    !isset($statInternet['bytes_amount'])
                    || !isset($statInternet['bytes_consumed'])
                ) {
                    continue;
                }

                if (!isset($alts[$statInternet['account_tariff_light_id']])) {
                    continue;
                }

                $internetDataCache[$did][$alts[$statInternet['account_tariff_light_id']]] = [
                    'bytes_amount' => $statInternet['bytes_amount'],
                    'bytes_consumed' => $statInternet['bytes_consumed'],
                ];
            }
        }

        return $internetDataCache[$did][$this->id];
    }


    /**
     * Прверяет конфигурацию пакетов в соответствии с текущим бандл-тарифом
     * @param array|integer $params AccountTariffId
     * @throws \yii\db\Exception
     */
    public static function actualizeBundlePackages($params)
    {
        if (($params['old_tariff_period_id'] ?? 0) == ($params['new_tariff_period_id'] ?? 0)) {
            HandlerLogger::me()->add('old=new');
            return;
        }

        $accountTariffId = null;
        if (is_array($params) && isset($params['account_tariff_id'])) {
            $accountTariffId = $params['account_tariff_id'];
        }

        $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
        if (!$accountTariff) {
            throw new InvalidParamException('Услуга не найдена: ' . $accountTariffId);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $accountTariff->_checkBoundlePackages();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function _checkBoundlePackages()
    {
        $packageType = ServiceType::$serviceToPackage[$this->service_type_id] ?? null;

        if (!$packageType) {
            return;
        }

        if (!$this->tariff_period_id) {
            HandlerLogger::me()->add('is off');
            return;
        }

        $tariff = $this->tariffPeriod->tariff;
        $bundleTariffs = [];
        foreach ($tariff->bundlePackages as $bundlePackage) {
            $bundleTariffPeriod = reset($bundlePackage->packageTariff->tariffPeriods); // нет механизма какой ТП брать
            $bundleTariffs[$bundleTariffPeriod->id] = $bundlePackage->packageTariff;
        }

        $needClose = [];
        $_needClose = [];
        foreach ($this->nextAccountTariffs as $nextAccountTariff) {
            if (!$nextAccountTariff->isActive()) {
                HandlerLogger::me()->add($nextAccountTariff->id . ' !isActive');
                // зачем нам уже отключенные. Включенные в будущем будут здесь
                continue;
            }

            $nextTariffPeriod = $nextAccountTariff->getNotNullTariffPeriod();

            if (isset($bundleTariffs[$nextTariffPeriod->id])) {
                echo PHP_EOL . 'unset($bundleTariffs[' . $nextTariffPeriod->id . ']';
                HandlerLogger::me()->add($nextAccountTariff->id . ' unset');
                unset($bundleTariffs[$nextTariffPeriod->id]);
                continue;
            }

            if (!$nextTariffPeriod->tariff->is_bundle) {
                HandlerLogger::me()->add($nextAccountTariff->id . ' !is_bundle');
                continue;
            }

            $needClose[] = $nextAccountTariff;
        }

        if ($bundleTariffs) {
            foreach ($bundleTariffs as $tariff) {
                HandlerLogger::me()->add('on: (' . $tariff->id . ')' . $tariff->name);
                $this->_addPackage($tariff);
            }
        }

        if ($needClose) {
            $accountTariffLogs = $this->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs); // пакет должен быть закрыт с даты закрытия самого тарифа (то есть начала desc-списка)
            if (!$accountTariffLog->tariff_period_id) {
                throw new \LogicException('Услуга ' . $this->id . ' закрыта, хотя не должна');
//                return;
            }
            foreach ($needClose as $nextAccountTariff) {
                HandlerLogger::me()->add('off: (' . $nextAccountTariff->id . ')' . $nextAccountTariff->tariffPeriod->getName() . ' - ' . $accountTariffLog->actual_from_utc);
                $this->_closePackage($nextAccountTariff, $accountTariffLog->actual_from_utc);
            }
        }
    }

}