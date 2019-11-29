<?php

namespace app\modules\sbisTenzor\helpers;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\Organization;
use app\modules\sbisTenzor\models\SBISContractor;
use app\modules\sbisTenzor\classes\SBISTensorAPI;
use app\modules\sbisTenzor\models\SBISExchangeGroup;
use app\modules\sbisTenzor\models\SBISOrganization;
use app\modules\sbisTenzor\Module;
use kartik\base\Config;

class SBISInfo
{
    /**
     * Проверка клиента на ошибки
     *
     * @param ClientAccount $client
     * @param Organization|null $organization
     * @param bool $isForce
     * @return string
     */
    public static function getClientError(ClientAccount $client, Organization $organization = null, $isForce = false)
    {
        $sbisOrganization = SBISDataProvider::getSBISOrganizationByClient($client, $organization);
        if (!$sbisOrganization) {
            $organization = $organization ? : $client->organization;
            return sprintf('Обслуживающая организация %s не настроена для работы со СБИС', $organization->name);
        }

        $groupIds = SBISInfo::getExchangeGroupsByClient($client, $sbisOrganization);
        if (empty($groupIds)) {
            return sprintf('Для данного клиента нет подходящих документов для отправки в СБИС');
        }

        $inn = $client->contragent->inn;
        if (!$inn) {
            return 'У контрагента данного клиента не заполнен ИНН!';
        }

        switch ($client->contragent->legal_type) {
            case ClientContragent::LEGAL_TYPE:
                if (!@preg_match('/^(([0-9]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{8})$/', $inn)) {
                    return sprintf('У контрагента данного клиента ИНН не соответствует формату (10 цифр для ЮЛ): "%s"!', $inn);
                }

                $kpp = $client->contragent->kpp;
                if (!$kpp) {
                    return 'У контрагента данного клиента не заполнен КПП!';
                }
                if (!@preg_match('/^(([0-9]{1}[1-9]{1}|[1-9]{1}[0-9]{1})([0-9]{2})([0-9A-Z]{2})([0-9]{3}))$/', $kpp)) {
                    return sprintf('У контрагента данного клиента КПП не соответствует формату (9 символов): "%s"!', $kpp);
                }

                break;

            case ClientContragent::IP_TYPE:
            case ClientContragent::PERSON_TYPE:
                if (!@preg_match('/^(([0-9]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{10})$/', $inn)) {
                    return sprintf('У контрагента данного клиента ИНН не соответствует формату (12 цифр для ИП/ФЛ): "%s"!', $inn);
                }

                break;
        }

        $ready = false;
        try {
            $ready = SBISInfo::checkExchangeIntegration($client, $isForce);
        } catch (\Exception $e) {}

        if (!$ready) {
            return sprintf('Данный клиент не зарегистрирован в системе докуметооборота СБИС');
        }

        return '';
    }

    /**
     * Получить доступные группы обмена первичными документами
     *
     * @param ClientAccount $client
     * @param SBISOrganization|null $sbisOrganization
     * @return array
     */
    public static function getExchangeGroupsByClient(ClientAccount $client, SBISOrganization $sbisOrganization = null)
    {
        $sbisOrganization = $sbisOrganization ? : SBISDataProvider::getSBISOrganizationByClient($client);

        // список документов по организации
        $groupIds1 = [];
        switch ($sbisOrganization->id) {
            case SBISOrganization::ID_MCN_TELECOM:
                $groupIds1 = [
                    SBISExchangeGroup::ACT_AND_INVOICE_2016,
                    SBISExchangeGroup::ACT_AND_INVOICE_2019,
                ];
                break;

            case SBISOrganization::ID_MCN_TELECOM_SERVICE:
                $groupIds1 = [
                    SBISExchangeGroup::ACT,
                ];
                break;
        }

        // список документов по форме регистрации
        $groupIds2 = [];
        switch ($client->contragent->legal_type) {
            case ClientContragent::LEGAL_TYPE:
                $groupIds2 = [
                    SBISExchangeGroup::ACT,
                    SBISExchangeGroup::ACT_AND_INVOICE_2016,
                    SBISExchangeGroup::ACT_AND_INVOICE_2019,
                ];
                break;

            case ClientContragent::IP_TYPE:
                $groupIds2 = [
                    SBISExchangeGroup::ACT,
                    SBISExchangeGroup::ACT_AND_INVOICE_2016,
                    SBISExchangeGroup::ACT_AND_INVOICE_2019,
                ];
                break;

            case ClientContragent::PERSON_TYPE:
                $groupIds2 = [
                    SBISExchangeGroup::ACT,
                ];
                break;
        }

        return array_intersect($groupIds1, $groupIds2);
    }

    /**
     * Проверка регистрации клиента в ЭДО
     *
     * @param ClientAccount $client
     * @param bool $isForce
     * @return bool
     * @throws \app\modules\sbisTenzor\exceptions\SBISTensorException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public static function checkExchangeIntegration(ClientAccount $client, $isForce = false)
    {
        return !empty(self::getExchangeIntegrationId($client, $isForce));
    }

    /**
     * Получить Идентификатор в ЭДО
     *
     * @param ClientAccount $client
     * @param bool $isForce
     * @return string
     * @throws \app\modules\sbisTenzor\exceptions\SBISTensorException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \Exception
     */
    public static function getExchangeIntegrationId(ClientAccount $client, $isForce = false)
    {
        $edoId = '';

        $sbisContractor = SBISDataProvider::getSBISContractor($client);
        if ($sbisContractor) {
            $edoId = $sbisContractor->exchange_id;
        } else {
            $isForce = true;
            $sbisContractor = new SBISContractor();
        }

        if ($isForce) {
            /** @var Module $module */
            $module = Config::getModule('sbisTenzor');
            $params = $module->getParams();

            $sbisOrganization = array_shift($params);
            $api = new SBISTensorAPI($sbisOrganization);

            $result = $api->getContractorInfo($client);
            $edoId = self::saveSBISContractorInfo($sbisContractor, $result);
        }

        return $edoId;
    }

    /**
     * Сохранить запись о контрагенте
     *
     * @param SBISContractor $sbisContractor
     * @param array $result
     * @return string
     * @throws \Exception
     */
    protected static function saveSBISContractorInfo(SBISContractor $sbisContractor, $result)
    {
        if (!array_key_exists('Идентификатор', $result)) {
            return '';
        }

        $fieldsMap = [
            'exchange_id' => 'Идентификатор',
            'exchange_id_is' => 'ИдентификаторИС',
            'exchange_id_spp' => 'ИдентификаторСПП',
            'email' => 'Email',
            'phone' => 'Телефон',
        ];
        foreach ($fieldsMap as $field => $property) {
            if (!empty($result[$property])) {
                $sbisContractor->$field = $result[$property];
            }
        }
        $sbisContractor->is_private = '0';

        if (!empty($result['СвЮЛ'])) {
            $sbisContractor->tin = $result['СвЮЛ']['ИНН'];
            $sbisContractor->iec = $result['СвЮЛ']['КПП'];
            $sbisContractor->country_code = $result['СвЮЛ']['КодСтраны'];
            $sbisContractor->full_name = $result['СвЮЛ']['Название'];
        }

        if (!empty($result['СвФЛ'])) {
            $sbisContractor->itn = $result['СвФЛ']['ИНН'];
            $sbisContractor->inila = $result['СвФЛ']['СНИЛС'];
            $sbisContractor->last_name = $result['СвФЛ']['Фамилия'];
            $sbisContractor->first_name = $result['СвФЛ']['Имя'];
            $sbisContractor->middle_name = $result['СвФЛ']['Отчество'];
            $sbisContractor->is_private = ($result['СвФЛ']['ЧастноеЛицо'] === 'Да' ? 1 : 0);

            $sbisContractor->full_name =
                ($sbisContractor->is_private ? '' : 'ИП ') .
                implode(' ', [
                    $result['СвФЛ']['Фамилия'],
                    $result['СвФЛ']['Имя'],
                    $result['СвФЛ']['Отчество'],
                ]);
        }

        if (!$sbisContractor->save()) {
            throw new ModelValidationException($sbisContractor);
        }

        return $result['Идентификатор'];
    }
}