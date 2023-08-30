<?php

namespace app\modules\sbisTenzor\helpers;

use app\classes\Connection;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\modules\sbisTenzor\exceptions\SBISTensorException;
use app\modules\sbisTenzor\models\SBISContractor;
use app\modules\sbisTenzor\classes\SBISTensorAPI;
use app\modules\sbisTenzor\models\SBISContractorExchange;
use app\modules\sbisTenzor\models\SBISExchangeGroup;
use app\modules\sbisTenzor\models\SBISOrganization;
use app\modules\sbisTenzor\Module;
use kartik\base\Config;
use yii\db\Expression;

class SBISInfo
{
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
                    SBISExchangeGroup::ACT_AND_INVOICE_2019,
                ];
                break;

            case SBISOrganization::ID_MCN_TELECOM_SERVICE:
            case SBISOrganization::ID_AB_SERVICE_MARCOMNET:
                $groupIds1 = [
                    SBISExchangeGroup::ACT,
                ];
                break;
        }

        // список документов по форме регистрации
        $groupIds2 = [];
        switch ($client->contragent->legal_type) {
            case ClientContragent::LEGAL_TYPE:
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
     * Получить Идентификатор в ЭДО
     *
     * @param ClientAccount $client
     * @param bool $isForce
     * @return string|null
     * @throws SBISTensorException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \Exception
     */
    public static function getExchangeIntegrationId(ClientAccount $client, $isForce = false)
    {
        return self::getPreparedContractor($client, $isForce)->getEdfId();
    }

    /**
     * Получить данные по контрагенту
     *
     * @param ClientAccount $client
     * @param bool $isForce
     * @return SBISContractor
     * @throws SBISTensorException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \Exception
     */
    public static function getPreparedContractor(ClientAccount $client, $isForce = false)
    {
        $sbisContractor = SBISDataProvider::getSBISContractor($client);
        if (!$sbisContractor) {
            $isForce = true;
            $sbisContractor = new SBISContractor();
            $sbisContractor->is_roaming = false;
        }

        if ($isForce) {
            $sbisContractor->account_id = $client->id;
            $sbisContractor->addAccountId($client->id);

            /** @var Module $module */
            $module = Config::getModule('sbisTenzor');
            if ($params = $module->getParams()) {
                $sbisOrganization = array_shift($params);
                $api = new SBISTensorAPI($sbisOrganization);

                $result = $api->getContractorInfo($client);
                self::prepareAndSaveContractor($sbisContractor, $result);
            }
        }

        return $sbisContractor;
    }

    /**
     * Сохранить запись о контрагенте
     *
     * @param SBISContractor $sbisContractor
     * @param array $result
     * @throws \Exception
     */
    protected static function prepareAndSaveContractor(SBISContractor $sbisContractor, $result)
    {
        if (!array_key_exists('Идентификатор', $result)) {
            return;
        }

        $fieldsMap = [
//            'exchange_id' => 'Идентификатор',
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
//        $sbisContractor->exchange_id = $sbisContractor->getMainExchangeId($result);
        $sbisContractor->is_private = '0';

        if (!empty($result['СвЮЛ'])) {
            $sbisContractor->tin = $result['СвЮЛ']['ИНН'];
            $sbisContractor->iec = $result['СвЮЛ']['КПП'];
            $sbisContractor->country_code = $result['СвЮЛ']['КодСтраны'];
            $sbisContractor->full_name = $result['СвЮЛ']['Название'];
            if (!empty($result['СвЮЛ']['КодФилиала'])) {
                $sbisContractor->branch_code = $result['СвЮЛ']['КодФилиала'];
            }
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

        self::saveContractorExchanges($sbisContractor, $result);

    }

    private static function saveContractorExchanges(SBISContractor $contractor, $result)
    {
        \Yii::$app->db->transaction(function(Connection $db) use($contractor, $result) {

            $exs = array_map(function($row) use ($contractor) {
                return [
                    $contractor->id,
                    $row['ИдентификаторУчастника'],
                    $row['Оператор']['Название'],
                    (int)($row['Основной'] == 'Да'),
                    (int)($row['Роуминг'] == 'Да'),
                    $row['СостояниеПодключения']['Код'],
                    $row['СостояниеПодключения']['Описание'],
                    new Expression('UTC_TIMESTAMP()')
                ];

            }, $result['Идентификатор']);

            $db->createCommand()->delete(SBISContractorExchange::tableName(), ['contractor_id' => $contractor->id])->execute();
            if ($exs) {
                $db->createCommand()->batchInsert(SBISContractorExchange::tableName(), [
                    'contractor_id', 'exchange_id', 'operator_name', 'is_main', 'is_roaming', 'exchange_state_code', 'exchange_state_code_description', 'created_at'
                ], $exs)->execute();
            }
        });
    }
}
