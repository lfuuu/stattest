<?php

namespace app\modules\uu\classes;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\Number;
use app\models\Trouble;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffTags;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipNdcType;
use app\modules\uu\Module as uuModule;
use Yii;
use DateTimeZone;
use Exception;
use yii\web\HttpException;

/**
 * Class Dao
 * @package app\modules\uu\classes
 *
 * @method static Dao me($args = null)
 */
class Dao extends Singleton
{
    public function addAccountTariff($post = [])
    {
        $transaction = Yii::$app->db->beginTransaction();
        $accountTariff = new AccountTariff();
        $accountTariffLog = new AccountTariffLog();

        try {

            $accountTariff->setAttributes($post);
            if (!$accountTariff->save()) {
                throw new ModelValidationException($accountTariff, $accountTariff->errorCode);
            }

            // записать в лог тарифа
            $accountTariffLog->account_tariff_id = $accountTariff->id;
            $accountTariffLog->setAttributes($post);
            if (!$accountTariffLog->actual_from_utc) {
                $accountTariffLog->actual_from_utc = (new \DateTime('00:00:00', $accountTariff->clientAccount->getTimezone()))
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);
            }

            if (!$accountTariffLog->save()) {
                throw new ModelValidationException($accountTariffLog, $accountTariffLog->errorCode);
            }

            $this->_checkTariff($accountTariff, $accountTariffLog);

            Trouble::dao()->notificateCreateAccountTariff($accountTariff, $accountTariffLog);

            $transaction->commit();
            return $accountTariff->id;
        } catch (Exception $e) {
            $transaction->rollBack();
            $code = $e->getCode();
            if ($code >= AccountTariff::ERROR_CODE_DATE_PREV && $code < AccountTariff::ERROR_CODE_USAGE_EMPTY) {
                \Yii::error(
                    print_r(['AddAccountTariff', $e->getMessage(), $post], true),
                    uuModule::LOG_CATEGORY_API
                );
            }

            $post['error'] = $e->getMessage();
            $post['file'] = $e->getFile() . ':' . $e->getLine();
            Trouble::dao()->notificateCreateAccountTariff($accountTariff, $accountTariffLog, $post);

            throw $e;
        }
    }

    private function _checkTariff($accountTariff, $accountTariffLog)
    {
        if (!$accountTariffLog->tariff_period_id) {
            // закрыть можно
            // @todo дефолтный пакет закрыть нельзя
            return;
        }

        if ($accountTariffLog->tariffPeriod->tariff->isTest) {
            // тестовый можно подключать
            // @todo на самом деле можно не всем, а только "заказ услуг" и "подключаемый". А вот "включенным" нельзя
            return;
        }

        // проверить папку "публичный" (вернее, в соответствии с уровнем цен УЛС)
        $tariffs = $this->actionGetTariffs(
            $idTmp = null,
            $accountTariff->service_type_id,
            $countryIdTmp = null,
            $clientAccountIdTmp = null,
            $currencyIdTmp = null,
            $isDefaultTmp = null,
            $isPostpaidTmp = null,
            $isOneActiveTmp = null,
            $tariffStatusIdTmp = null,
            $tariffPersonIdTmp = null,
            $tariffTagIdTmp = null,
            $tariffTagsIdTmp = null,
            $voipGroupIdTmp = null,
            $voipCityIdTmp = null,
            $voipNdcTypeIdTmp = null,
            $organizationIdTmp = null,
            $voipNumberTmp = null,
            $accountTariffIdTmp = $accountTariff->id
        );

        foreach ($tariffs as $tariff) {
            foreach ($tariff['tariff_periods'] as $tariffPeriod) {
                if ($tariffPeriod['id'] == $accountTariffLog->tariff_period_id) {
                    return;
                }
            }
        }

        throw new HttpException(ModelValidationException::STATUS_CODE, 'Тариф недоступен этому ЛС', AccountTariff::ERROR_CODE_TARIFF_WRONG);
    }

    public function actionGetTariffs(
        $id = null,
        $service_type_id = null,
        $country_id = null,
        $client_account_id = null,
        $currency_id = null,
        $is_default = null,
        $is_postpaid = null,
        $is_one_active = null,
        $tariff_status_id = null,
        $tariff_person_id = null,
        $tariff_tag_id = null,
        $tariff_tags_id = null,
        $voip_group_id = null,
        $voip_city_id = null,
        $voip_ndc_type_id = null,
        $organization_id = null,
        $voip_number = null,
        $account_tariff_id = null,
        $is_include_vat = null
    )
    {
        \Yii::info(
            print_r([
                'actionGetTariffs',
                $id,
                $service_type_id,
                $country_id,
                $client_account_id,
                $currency_id,
                $is_default,
                $is_one_active,
                $is_postpaid,
                $tariff_status_id,
                $tariff_person_id,
                $tariff_tag_id,
                $tariff_tags_id,
                $voip_group_id,
                $voip_city_id,
                $voip_ndc_type_id,
                $organization_id,
                $voip_number,
                $account_tariff_id
            ], true),
            uuModule::LOG_CATEGORY_API
        );

        $id = (int)$id;
        $service_type_id = (int)$service_type_id;
        $is_one_active = (int)$is_one_active;

        $country_id = (int)$country_id;
        $client_account_id = (int)$client_account_id;
        $tariff_status_id = (int)$tariff_status_id;
        $tariff_person_id = (int)$tariff_person_id;
        $tariff_tag_id = (int)$tariff_tag_id;
        if ($tariff_tags_id && !is_numeric($tariff_tags_id) && !is_array($tariff_tags_id)) {
            $tariff_tags_id = preg_split('/\D+/', $tariff_tags_id);
        }

        $voip_group_id = (int)$voip_group_id;
        $voip_city_id = (int)$voip_city_id;
        $voip_ndc_type_id = (int)$voip_ndc_type_id;
        $organization_id = (int)$organization_id;
        $account_tariff_id = (int)$account_tariff_id;

        if ($account_tariff_id) {
            /** @var AccountTariff $accountTariff */
            $accountTariff = AccountTariff::find()->where(['id' => $account_tariff_id])->one();
            if (!$accountTariff) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный account_tariff_id', AccountTariff::ERROR_CODE_USAGE_MAIN);
            }

            $client_account_id = $accountTariff->client_account_id;
            $voip_number = $accountTariff->prev_account_tariff_id ?
                $accountTariff->prevAccountTariff->voip_number :
                $accountTariff->voip_number;
            $voip_city_id = $accountTariff->city_id;
            if ($accountTariff->city_id) {
                $country_id = $accountTariff->city->country_id;
            }
        }

        if ($client_account_id) {

            $clientAccount = ClientAccount::findOne(['id' => $client_account_id]);
            if (!$clientAccount) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный client_account_id', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
            }

            if (!$currency_id) {
                $currency_id = $clientAccount->currency;
            }

            $is_postpaid = $clientAccount->is_postpaid;
            $organization_id = $clientAccount->contract->organization_id;

            $tariff_person_id = ($clientAccount->contragent->legal_type == ClientContragent::PERSON_TYPE) ?
                TariffPerson::ID_NATURAL_PERSON :
                TariffPerson::ID_LEGAL_PERSON;

            $country_id = $clientAccount->getUuCountryId();

            switch ($service_type_id) {

                case ServiceType::ID_VOIP:
                    if (!$voip_number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Не указан телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    /** @var \app\models\Number $number */
                    $number = Number::findOne(['number' => $voip_number]);
                    if (!$number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    if (!$account_tariff_id && $number->status != Number::STATUS_INSTOCK) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Телефонный номер уже занят', AccountTariff::ERROR_CODE_USAGE_NUMBER_NOT_IN_STOCK);
                    }

                    $tariff_status_id = $number->didGroup->getTariffStatusMain($clientAccount->price_level);
                    $voip_ndc_type_id = $number->ndc_type_id;
                    break;

                case ServiceType::ID_VOIP_PACKAGE_CALLS:
                    if (!$voip_number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Не указан телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    /** @var \app\models\Number $number */
                    $number = Number::findOne(['number' => $voip_number]);
                    if (!$number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    $tariff_status_id = $number->didGroup->getTariffStatusPackage($clientAccount->price_level);
                    $voip_ndc_type_id = $number->ndc_type_id;
                    break;
            }
        }

        // @todo надо ли только статус "публичный" для ватс?

        $tariffQuery = Tariff::find();
        $tariffTableName = Tariff::tableName();

        $id && $tariffQuery->andWhere([$tariffTableName . '.id' => $id]);
        $service_type_id && $tariffQuery->andWhere([$tariffTableName . '.service_type_id' => (int)$service_type_id]);
        $currency_id && $tariffQuery->andWhere([$tariffTableName . '.currency_id' => $currency_id]);
        !is_null($is_default) && $tariffQuery->andWhere([$tariffTableName . '.is_default' => (int)$is_default]);
        !is_null($is_postpaid) && $tariffQuery->andWhere([$tariffTableName . '.is_postpaid' => (int)$is_postpaid]);
        !is_null($is_one_active) && $tariffQuery->andWhere([$tariffTableName . 'is_one_active' => (int)$is_one_active]);
        !is_null($is_include_vat) && $tariffQuery->andWhere([$tariffTableName . '.is_include_vat' => (int)$is_include_vat]);
        $tariff_status_id && $tariffQuery->andWhere([$tariffTableName . '.tariff_status_id' => (int)$tariff_status_id]);
        $tariff_person_id && $tariffQuery->andWhere([$tariffTableName . '.tariff_person_id' => [TariffPerson::ID_ALL, $tariff_person_id]]);
        $tariff_tag_id && $tariffQuery->andWhere([$tariffTableName . '.tariff_tag_id' => $tariff_tag_id]);
        if ($tariff_tags_id) {
            $tariffQuery->joinWith('tariffTags')
                ->andWhere([TariffTags::tableName() . '.tag_id' => $tariff_tags_id]);
        }
        $voip_group_id && $tariffQuery->andWhere([$tariffTableName . '.voip_group_id' => (int)$voip_group_id]);

        if ($country_id) {
            $tariffQuery
                ->joinWith('tariffCountries')
                ->andWhere([TariffCountry::tableName() . '.country_id' => $country_id]);
        }

        if ($voip_city_id) {
            $tariffQuery
                ->joinWith('voipCities')
                ->andWhere([
                    'OR',
                    [TariffVoipCity::tableName() . '.city_id' => $voip_city_id], // если в тарифе хоть один город, то надо только точное соотвествие
                    [TariffVoipCity::tableName() . '.city_id' => null] // если в тарифе ни одного города нет, то это означает "любой город этой страны"
                ]);
        }

        if ($voip_ndc_type_id) {
            $tariffQuery
                ->joinWith('voipNdcTypes')
                ->andWhere([TariffVoipNdcType::tableName() . '.ndc_type_id' => $voip_ndc_type_id]);
        }

        if ($organization_id) {
            $tariffQuery
                ->joinWith('organizations')
                ->andWhere([TariffOrganization::tableName() . '.organization_id' => $organization_id]);
        }

        $result = [];
        /** @var Tariff $tariff */
        foreach ($tariffQuery->each() as $tariff) {

            if ($tariff->service_type_id == ServiceType::ID_VOIP) {
                $defaultPackageRecords = $this->actionGetTariffs(
                    $id_tmp = null,
                    ServiceType::ID_VOIP_PACKAGE_CALLS,
                    $country_id, // пакеты телефонии - по стране, все остальное - по организации
                    $client_account_id,
                    $currency_id,
                    $is_default_tmp = 1,
                    $is_postpaid_tmp = null,
                    $is_one_active,
                    $tariff_status_id,
                    $tariff_person_id,
                    $tariff_tag_id_tmp = null,
                    $tariff_tags_id_tmp = null,
                    $voip_group_id,
                    $voip_city_id,
                    $voip_ndc_type_id,
                    $organization_id_tmp = null, // пакеты телефонии - по стране, все остальное - по организации
                    $voip_number,
                    $account_tariff_id
                );
            } else {
                $defaultPackageRecords = [];
            }

            $tariffRecord = $this->_light_getTariffRecord($tariff, $tariff->tariffPeriods);
            $tariffRecord['default_packages'] = $defaultPackageRecords;
            $result[] = $tariffRecord;
        }

        return $result;
    }

    private function _light_getTariffRecord(Tariff $tariff, $tariffPeriods)
    {
        return [
            'id' => $tariff->id,
            'tariff_periods' => $this->_light_getTariffPeriodRecord($tariffPeriods),
        ];
    }

    private function _light_getTariffPeriodRecord($model)
    {
        if (is_array($model)) {
            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_light_getTariffPeriodRecord($subModel);
            }

            return $result;
        }

        if ($model) {
            return [
                'id' => $model->id,
//                'price_setup' => $model->price_setup,
//                'price_per_period' => $model->price_per_period,
//                'price_per_charge_period' => round($model->price_per_period * ($model->chargePeriod->monthscount ?: 1 / 30), 2),
//                'price_min' => $model->price_min,
//                'charge_period' => $this->_getIdNameRecord($model->chargePeriod),
            ];
        }

        return null;

    }

}