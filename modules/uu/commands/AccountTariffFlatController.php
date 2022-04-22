<?php

namespace app\modules\uu\commands;

use app\exceptions\ModelValidationException;
use app\models\DidGroup;
use app\modules\nnp\models\NdcType;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffFlat;
use app\modules\uu\models\AccountTrouble;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffStatus;
use yii\console\Controller;
use app\models\Currency;
use app\models\City;
use app\models\Region;

class AccountTariffFlatController extends Controller
{
    /**
     * @var array Список валют
     */
    private $_currencyList = [];

    /**
     * @var array Список регионов
     */
    private $_regionList = [];

    /**
     * @var array Список городов
     */
    private $_cityList = [];

    /**
     * @var array Список NdcType
     */
    private $_ndcTypeList = [];

    /**
     * @var array Список статусов услуг
     */
    private $_tariffStatusList = [];

    /**
     * Генерация полного flat-отчета
     */
    public function actionGenerate()
    {
        // Удаление старых данных
        AccountTariffFlat::deleteAll();
        // Инициализация списков валют, регионов, городов, ndc_type и статусов тарифа
        $this->_initLists();
        // Генерация отчетов нескольких типов
        $serviceTypes = [
            ServiceType::ID_VPBX => 'ВАТС',
            ServiceType::ID_VOIP => 'Телефония',
            ServiceType::ID_VOIP_PACKAGE_CALLS => 'Телефония. Пакет звонков',
            ServiceType::ID_VOIP_PACKAGE_SMS => 'Телефония. Пакет СМС',
            ServiceType::ID_VOIP_PACKAGE_INTERNET => 'Телефония. Пакет Интернета',
            ServiceType::ID_INTERNET => 'Интернет',
            ServiceType::ID_CALL_CHAT => 'Звонок-чат',
            ServiceType::ID_EXTRA => 'Дополнительные услуги',
            ServiceType::ID_WELLTIME_SAAS => 'elltime как сервис',
        ];
        foreach ($serviceTypes as $serviceTypeKey => $serviceTypeValue) {
            echo "Формирование услуги#{$serviceTypeValue}" . PHP_EOL;
            $filterModel = new AccountTariffFilter($serviceTypeKey);
            $filterModel = $filterModel->search();
            $filterModel->pagination = false;
            foreach ($filterModel->getModels() as $model) {
                /** @var AccountTariff $model */
                try {
                    $tariffPeriod = $model->tariffPeriod;
                    $tariff = $tariffPeriod ?
                        $tariffPeriod->tariff : null;
                    // Создание объекта плоской таблицы
                    $accountTariffFlat = new AccountTariffFlat;
                    // ID услуги
                    $accountTariffFlat->account_tariff_id = $model->id;
                    // Тип услуги
                    $accountTariffFlat->service_type = $serviceTypeValue;
                    // У-услуги
                    $accountTariffFlat->tariff_period = $model->getName(false);
                    // Включая НДС
                    $accountTariffFlat->tariff_is_include_vat = $tariff ?
                        $tariff->is_include_vat : null;
                    // Постоплата
                    $accountTariffFlat->tariff_is_postpaid = $model->clientAccount->is_postpaid;
                    // Страна
                    if ($tariff && $tariffCountries = $tariff->tariffCountries) {
                        $accountTariffFlat->tariff_country = call_user_func(function () use ($tariffCountries) {
                            $maxCount = 2;
                            $count = count($tariffCountries);
                            if ($count <= $maxCount--) {
                                return implode('<br/>', $tariffCountries);
                            }
                            return sprintf(
                                '%s<br/><abbr title="%s">… %d…</abbr>',
                                implode('<br/>', array_slice($tariffCountries, 0, $maxCount)),
                                implode(PHP_EOL, array_slice($tariffCountries, $maxCount)),
                                $count - $maxCount
                            );
                        });
                    }
                    // Валюта
                    if ($tariff && isset($this->_currencyList[$tariff->currency_id])) {
                        $accountTariffFlat->tariff_currency = $this->_currencyList[$tariff->currency_id];
                    }
                    // Организации
                    $accountTariffFlat->tariff_organization = $tariff ?
                        $tariff->getOrganizationsString() : null;
                    // По умолчанию
                    $accountTariffFlat->tariff_is_default = $tariff ?
                        $tariff->is_default : null;
                    // Статус тарифа
                    if ($tariff) {
                        $accountTariffFlat->tariff_status = isset($this->_tariffStatusList[$tariff->tariff_status_id]) ?
                            $this->_tariffStatusList[$tariff->tariff_status_id] : $tariff->tariff_status_id;
                    }
                    // УЛС
                    $accountTariffFlat->client_account = $model->clientAccount->getLink();
                    // Точка присоединения
                    $accountTariffFlat->region = isset($this->_regionList[$model->region_id]) ?
                        $this->_regionList[$model->region_id] : $model->region_id;
                    // Комментарий
                    $accountTariffFlat->comment = $model->comment;
                    // Дата последней смены тарифа
                    $accountTariffFlat->tariff_period_utc = $model->tariff_period_utc;
                    // Абонентка списана до
                    $accountTariffFlat->account_log_period_utc = $model->account_log_period_utc;
                    // Разархивированно для услуги ВАТС
                    if ($serviceTypeKey === ServiceType::ID_VPBX) {
                        $accountTariffFlat->is_unzipped = $model->is_unzipped;
                    }
                    // Город для услуг Телефония, Телефония.Пакет звонков
                    if (in_array($serviceTypeKey, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS])) {
                        $accountTariffFlat->city = isset($this->_cityList[$model->city_id]) ?
                            $this->_cityList[$model->city_id] : $model->city_id;
                        // Номер, красивость, Тип NDC для услуги Телефония
                        if ($serviceTypeKey === ServiceType::ID_VOIP) {
                            $accountTariffFlat->voip_number = $model->voip_number;
                            // Красивость
                            $number = $model->number;
                            if ($number) {
                                $accountTariffFlat->beauty_level = isset(DidGroup::$beautyLevelNames[$number->beauty_level]) ?
                                    DidGroup::$beautyLevelNames[$number->beauty_level] : $number->beauty_level;
                                // Тип NDC
                                $accountTariffFlat->ndc_type = isset($this->_ndcTypeList[$number->ndc_type_id]) ?
                                    $this->_ndcTypeList[$number->ndc_type_id] : $number->ndc_type_id;
                            }
                        }
                    }
                    // Тариф основной услуги для услуг Телефония. Пакет звонков, Телефония. Пакет СМС, Телефония. Пакет Интернета
                    if (in_array($serviceTypeKey, [ServiceType::ID_VOIP_PACKAGE_CALLS, ServiceType::ID_VOIP_PACKAGE_SMS, ServiceType::ID_VOIP_PACKAGE_INTERNET])) {
                        $prevModel = $model->prevAccountTariff;
                        $tariffPeriod = $prevModel ? $prevModel->tariffPeriod : null;
                        $accountTariffFlat->prev_account_tariff_tariff = $tariffPeriod ?
                            $tariffPeriod->getName() : null;
                    }
                    // Даты включения на тестовый тариф, продажи, допродажи, отключения
                    if ($modelHeap = $model->accountTariffHeap) {
                        $accountTariffFlat->test_connect_date = $modelHeap->test_connect_date;
                        $accountTariffFlat->date_sale = $modelHeap->date_sale;
                        $accountTariffFlat->date_before_sale = $modelHeap->date_before_sale;
                        $accountTariffFlat->disconnect_date = $modelHeap->disconnect_date;
                    }
                    // Ак. менеджер и Заявка ЛИД для ВАТС, Телефония, Звонок-чат
                    if (in_array($serviceTypeKey, [ServiceType::ID_VPBX, ServiceType::ID_VOIP, ServiceType::ID_CALL_CHAT])) {
                        $accountTariffFlat->account_manager_name = $model->clientAccount->contract->getAccountManagerName();
                        // Получение Лид'а
                        $accountTroublesQuery = AccountTrouble::find()
                            ->where(['account_tariff_id' => $model->id]);
                        $leads = [];
                        foreach ($accountTroublesQuery->each() as $accountTrouble) {
                            /** @var AccountTrouble $accountTrouble */
                            $leads[] = $accountTrouble->trouble_id;
                        }
                        if ($leads) {
                            $accountTariffFlat->lead = implode(', ', $leads);
                        }
                    }
                    if (!$accountTariffFlat->save()) {
                        throw new ModelValidationException($accountTariffFlat);
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }
            }
        }
    }

    /**
     * Инициализация список, что бы минимизировать запросы через связанные модели
     */
    private function _initLists()
    {
        $this->_currencyList = Currency::getList();
        $this->_regionList = Region::getList();
        $this->_cityList = City::getList();
        $this->_ndcTypeList = NdcType::getList();
        $this->_tariffStatusList = TariffStatus::getList(ServiceType::ID_VOIP);
    }
}
