<?php

namespace app\modules\uu\commands;

use app\exceptions\ModelValidationException;
use app\models\DidGroup;
use app\modules\nnp\models\NdcType;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffVoipFlat;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffStatus;
use yii\console\Controller;
use app\models\Currency;
use app\models\City;
use app\models\Region;

class AccountTariffVoipFlatController extends Controller
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
     * Генерация flat-отчета телефонии У-услуг
     */
    public function actionGenerate()
    {
        // Очистка таблицы
        AccountTariffVoipFlat::deleteAll();
        // Инициализация списков валют, регионов, городов, ndc_type и статусов тарифа
        $this->_initLists();

        $filterModel = new AccountTariffFilter(ServiceType::ID_VOIP);
        $filterModel = $filterModel->search();
        $filterModel->pagination = false;
        foreach ($filterModel->getModels() as $model) {
            try {
                /** @var AccountTariff $model */
                $tariffPeriod = $model->tariffPeriod;
                $tariff = $tariffPeriod ?
                    $tariffPeriod->tariff : null;
                // Создание объекта плоской таблицы
                $accountTariffVoipFlat = new AccountTariffVoipFlat;
                // ID услуги
                $accountTariffVoipFlat->account_tariff_id = $model->id;
                // У-услуги
                $accountTariffVoipFlat->tariff_period = $model->getName(false);
                // Включая НДС
                $accountTariffVoipFlat->tariff_is_include_vat = $tariff ?
                    $tariff->is_include_vat : null;
                // Постоплата
                $accountTariffVoipFlat->tariff_is_postpaid = $tariff ?
                    $tariff->is_postpaid : null;
                // Страна
                if ($tariff && $tariffCountries = $tariff->tariffCountries) {
                    $accountTariffVoipFlat->tariff_country = call_user_func(function () use ($tariffCountries) {
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
                    $accountTariffVoipFlat->tariff_currency = $this->_currencyList[$tariff->currency_id];
                }
                // Организации
                $accountTariffVoipFlat->tariff_organization = $tariff ?
                    $tariff->getOrganizationsString() : null;
                // По умолчанию
                $accountTariffVoipFlat->tariff_is_default = $tariff ?
                    $tariff->is_default : null;
                // Статус тарифа
                if ($tariff) {
                    $accountTariffVoipFlat->tariff_status = isset($this->_tariffStatusList[$tariff->tariff_status_id]) ?
                        $this->_tariffStatusList[$tariff->tariff_status_id] : $tariff->tariff_status_id;
                }
                // УЛС
                $accountTariffVoipFlat->client_account = $model->clientAccount->getLink();
                // Точка присоединения
                $accountTariffVoipFlat->region = isset($this->_regionList[$model->region_id]) ?
                    $this->_regionList[$model->region_id] : $model->region_id;
                // Комментарий
                $accountTariffVoipFlat->comment = $model->comment;
                // Дата последней смены тарифа
                $accountTariffVoipFlat->tariff_period_utc = $model->tariff_period_utc;
                // Абонентка списана до
                $accountTariffVoipFlat->account_log_period_utc = $model->account_log_period_utc;
                // Город
                $accountTariffVoipFlat->city = isset($this->_cityList[$model->city_id]) ?
                    $this->_cityList[$model->city_id] : $model->city_id;
                // Номер
                $accountTariffVoipFlat->voip_number = $model->voip_number;
                // Красивость
                $number = $model->number;
                if ($number) {
                    $accountTariffVoipFlat->beauty_level = isset(DidGroup::$beautyLevelNames[$number->beauty_level]) ?
                        DidGroup::$beautyLevelNames[$number->beauty_level] : $number->beauty_level;
                    // Тип NDC
                    $accountTariffVoipFlat->ndc_type = isset($this->_ndcTypeList[$number->ndc_type_id]) ?
                        $this->_ndcTypeList[$number->ndc_type_id] : $number->ndc_type_id;
                }
                // Дата включения на тестовый тариф
                $accountTariffVoipFlat->test_connect_date = $model->test_connect_date;
                // Дата продажи
                $accountTariffVoipFlat->date_sale = $model->date_sale;
                // Дата допродажи
                $accountTariffVoipFlat->date_before_sale = $model->date_before_sale;
                // Дата отключения
                $accountTariffVoipFlat->disconnect_date = $model->disconnect_date;
                // Аккаунт - менеджер
                $accountTariffVoipFlat->account_manager_name = $model->clientAccount->contract->getAccountManagerName();

                if (!$accountTariffVoipFlat->save()) {
                    throw new ModelValidationException($accountTariffVoipFlat);
                }
            } catch (ModelValidationException $e) {
                \Yii::error($e);
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