<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\api\internal\PartnerNotFoundException;
use app\exceptions\web\BadRequestHttpException;
use app\forms\client\ClientCreateExternalForm;
use app\helpers\DateTimeZoneHelper;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ClientSuper;
use Exception;
use Yii;

class ClientController extends ApiInternalController
{
    /**
     * @SWG\Definition(definition="contract", type="object", required={"id","number","accounts"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор договора"),
     *   @SWG\Property(property="number", type="string", description="Номер договора"),
     *   @SWG\Property(property="accounts", type="array", description="Массив идентификаторов лицевых счетов", @SWG\Items(type="integer"))
     * ),
     * @SWG\Definition(definition="contragent", type="object", required={"id","name","contracts"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор контрагента"),
     *   @SWG\Property(property="name", type="string", description="Название контрагента"),
     *   @SWG\Property(property="contracts", type="array", description="Массив договоров", @SWG\Items(ref="#/definitions/contract"))
     * ),
     * @SWG\Definition(definition="client", type="object", required={"id","name","contragents"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор супер-клиента"),
     *   @SWG\Property(property="name", type="string", description="Название супер-клиента"),
     *   @SWG\Property(property="contragents", type="array", description="Массив контрагентов", @SWG\Items(ref="#/definitions/contragent"))
     * ),
     * @SWG\Post(tags={"Работа с клиентами"}, path="/internal/client/", summary="Получение данных по клиенту", operationId="Получение данных по клиенту",
     *   @SWG\Parameter(name="client_id", type="integer", description="идентификатор супер-клиента", in="formData", default=""),
     *   @SWG\Response(response=200, description="данные о клиенте", @SWG\Schema(ref="#/definitions/client")),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     */
    public function actionIndex()
    {
        $superId = isset($this->requestData['client_id']) ? $this->requestData['client_id'] : null;

        if (!$superId) {
            throw new BadRequestHttpException;
        }

        if ($superId && ($super = ClientSuper::findOne(['id' => $superId]))
        ) {

            $contragents = [];
            foreach ($super->contragents as $c) {
                $contracts = [];
                foreach ($c->contracts as $cc) {
                    $accounts = [];
                    foreach ($cc->accounts as $a) {
                        $accounts[] = $a->id;
                    }

                    $contracts[] = ['id' => $cc->id, 'number' => $cc->number, 'accounts' => $accounts];
                }

                $contragents[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'contracts' => $contracts
                ];
            }

            $data = [
                'name' => $super->name,
                'id' => $super->id,
                'contragents' => $contragents
            ];

            return $data;
        } else {
            throw new BadRequestHttpException;
        }
    }

    /**
     * @SWG\Definition(definition="get-client-struct-applications", type="object", required={"id","name","enabled"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор"),
     *   @SWG\Property(property="name", type="string", description="Название"),
     *   @SWG\Property(property="enabled", type="boolean", description="Признак отключенного")
     * ),
     * @SWG\Definition(definition="get-client-struct-account", type="object", required={"id","is_partner","is_disabled","applications"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор ЛС"),
     *   @SWG\Property(property="partner_id", type="integer", description="Идентификатор договора партнера"),
     *   @SWG\Property(property="can_login_as_clients", type="boolean", description="Признак доступности ЛК"),
     *   @SWG\Property(property="is_partner", type="boolean", description="Признак партнерского договора"),
     *   @SWG\Property(property="partner_login_allow", type="boolean", description="Разрешен доступ в ЛК для партнера-родителя"),
     *   @SWG\Property(property="is_disabled", type="boolean", description="Признак отключенного"),
     *   @SWG\Property(property="version", type="integer", description="Версия биллера ЛС"),
     *   @SWG\Property(property="applications", type="array", description="Массив приложений", @SWG\Items(ref="#/definitions/get-client-struct-applications"))
     * ),
     * @SWG\Definition(definition="get-client-struct-contragent", type="object", required={"id","name","country","accounts"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор контрагента"),
     *   @SWG\Property(property="name", type="string", description="Имя контагента"),
     *   @SWG\Property(property="country", type="string", description="Страна"),
     *   @SWG\Property(property="accounts", type="array", description="Массив ЛС", @SWG\Items(ref="#/definitions/get-client-struct-account"))
     * ),
     * @SWG\Definition(definition="get-client-struct", type="object", required={"id","name","timezone","contragents"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор супер-клиента"),
     *   @SWG\Property(property="name", type="string", description="Название супер-клиента"),
     *   @SWG\Property(property="timezone", type="string", description="Таймзона"),
     *   @SWG\Property(property="contragents", type="array", description="Массив контрагентов", @SWG\Items(ref="#/definitions/get-client-struct-contragent"))
     * ),
     * @SWG\Post(tags={"Работа с клиентами"}, path="/internal/client/get-client-struct/", summary="Получение структуры клиента", operationId="Получение структуры клиента",
     *   @SWG\Parameter(name="id", type="integer", description="идентификатор супер-клиента", in="formData", default=""),
     *   @SWG\Parameter(name="name", type="string", description="имя супер-клиента", in="formData", default=""),
     *   @SWG\Parameter(name="contragent_id", type="integer", description="идентификатор контрагента", in="formData", default=""),
     *   @SWG\Parameter(name="contragent_name", type="string", description="имя контрагента", in="formData", default=""),
     *   @SWG\Parameter(name="account_id", type="integer", description="идентификатор ЛС", in="formData", default=""),
     *   @SWG\Response(response=200, description="данные о клиенте", @SWG\Schema(ref="#/definitions/get-client-struct")),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     *
     * @param int $id
     * @param string $name
     * @param int $contragent_id
     * @param string $contragent_name
     * @param int $account_id
     * @return array|bool
     */
    public function actionGetClientStruct(
        $id = null,
        $name = null,
        $contragent_id = null,
        $contragent_name = null,
        $account_id = null
    ) {

        foreach (['id', 'name', 'contragent_id', 'contragent_name', 'account_id'] as $var) {
            $$var = isset($this->requestData[$var]) ? $this->requestData[$var] : null;
        }

        $ids = [];
        if (empty($id)) {
            if (empty($name)) {
                if (empty($contragent_id)) {
                    if (empty($contragent_name)) {
                        if (empty($account_id)) {
                            return false;
                        } else {
                            $account = ClientAccount::findOne(['id' => $account_id]);
                            $ids[] = $account->super_id;
                        }
                    } else {
                        $ids = array_keys(ClientContragent::find()->andWhere(['name' => $contragent_name])->indexBy('super_id')->all());
                    }
                } else {
                    $contragent = ClientContragent::findOne(['id' => $contragent_id]);
                    $ids[] = $contragent->super_id;
                }
            } else {
                $ids = array_keys(ClientSuper::find()->andWhere(['name' => $name])->indexBy('id')->all());
            }
        } else {
            $ids[] = $id;
        }

        $fullResult = [];
        foreach ($ids as $idTmp) {
            $super = ClientSuper::find()->where(['id' => $idTmp])->with('contragents')->with('contracts')->with('accounts')->one();

            $timezone = DateTimeZoneHelper::TIMEZONE_MOSCOW;

            $resultContragents = [];

            /** @var ClientContragent $contragent */
            foreach ($super->contragents as $contragent) {
                $resultAccounts = [];
                /** @var ClientContract $contract */
                foreach ($contragent->contracts as $contract) {
                    /** @var ClientAccount $account */
                    foreach ($contract->accounts as $account) {
                        $resultAccounts[] = [
                            'id' => $account->id,
                            'is_disabled' => $contract->business_process_status_id != BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
                            'partner_id' => $contract->isPartnerAgent(),
                            'can_login_as_clients' => $contract->is_lk_access,
                            'is_partner' => $contract->isPartner(),
                            'partner_login_allow' => $contract->is_partner_login_allow,
                            'version' => $account->account_version,
                            'applications' => $this->_getPlatformaServices($account->client)
                        ];
                        $timezone = $account->timezone_name;
                    }
                }

                if ($resultAccounts) {
                    $resultContragents[] = [
                        'id' => $contragent->id,
                        'name' => $contragent->name,
                        'country' => $contragent->country->alpha_3,
                        'accounts' => $resultAccounts
                    ];
                }
            }

            if ($resultContragents) {
                $fullResult[] = [
                    'id' => $super->id,
                    'timezone' => $timezone,
                    'name' => $super->name,
                    'contragents' => $resultContragents
                ];
            }
        }

        return $fullResult;
    }

    /**
     * @SWG\Definition(definition="get-full-client-struct-applications", type="object", required={"id","name","is_enabled"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор"),
     *   @SWG\Property(property="name", type="string", description="Название"),
     *   @SWG\Property(property="is_enabled", type="boolean", description="Признак включенного")
     * ),
     *
     * @SWG\Definition(definition="get-full-client-struct-account", type="object", required={"id","is_disabled","version","applications"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор ЛС"),
     *   @SWG\Property(property="is_disabled", type="boolean", description="Признак отключенного"),
     *   @SWG\Property(property="version", type="integer", description="Версия биллера ЛС"),
     *   @SWG\Property(property="applications", type="array", description="Массив приложений", @SWG\Items(ref="#/definitions/get-full-client-struct-applications"))
     * ),
     *
     * @SWG\Definition(definition="get-full-client-struct-contract", type="object", required={"id","number","state","is_partner","accounts"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор договора"),
     *   @SWG\Property(property="number", type="string", description="Номер договора"),
     *   @SWG\Property(property="state", type="string", description="Состояние договора"),
     *   @SWG\Property(property="can_login_as_clients", type="boolean", description="Признак доступности ЛК"),
     *   @SWG\Property(property="partner_id", type="integer", description="Идентификатор договора партнера"),
     *   @SWG\Property(property="is_partner", type="boolean", description="Признак партнерского договора"),
     *   @SWG\Property(property="partner_login_allow", type="boolean", description="Разрешен доступ в ЛК для партнера-родителя"),
     *   @SWG\Property(property="accounts", type="array", description="Массив ЛС", @SWG\Items(ref="#/definitions/get-full-client-struct-account"))
     * ),
     *
     * @SWG\Definition(definition="get-full-client-struct-contragent", type="object", required={"id","name","country","contracts"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор контрагента"),
     *   @SWG\Property(property="name", type="string", description="Имя контагента"),
     *   @SWG\Property(property="country", type="string", description="Страна"),
     *   @SWG\Property(property="contracts", type="array", description="Массив ЛС", @SWG\Items(ref="#/definitions/get-full-client-struct-contract"))
     * ),
     *
     * @SWG\Definition(definition="get-full-client-struct", type="object", required={"id","name","timezone","contragents"},
     *   @SWG\Property(property="id", type="integer", description="Идентификатор супер-клиента"),
     *   @SWG\Property(property="name", type="string", description="Название супер-клиента"),
     *   @SWG\Property(property="timezone", type="string", description="Таймзона"),
     *   @SWG\Property(property="contragents", type="array", description="Массив контрагентов", @SWG\Items(ref="#/definitions/get-full-client-struct-contragent"))
     * ),
     *
     * @SWG\Post(tags={"Работа с клиентами"}, path="/internal/client/get-full-client-struct/", summary="Получение полной структуры клиента", operationId="Получение полной структуры клиента",
     *   @SWG\Parameter(name="id", type="integer", description="идентификатор супер-клиента", in="formData", default=""),
     *   @SWG\Parameter(name="name", type="string", description="имя супер-клиента", in="formData", default=""),
     *   @SWG\Parameter(name="contract_id[0]", type="integer", description="идентификатор договора", in="formData", default=""),
     *   @SWG\Parameter(name="contract_id[1]", type="integer", description="идентификатор договора", in="formData", default=""),
     *   @SWG\Parameter(name="contragent_id[0]", type="integer", description="идентификатор контрагента", in="formData", default=""),
     *   @SWG\Parameter(name="contragent_id[1]", type="integer", description="идентификатор контрагента", in="formData", default=""),
     *   @SWG\Parameter(name="contragent_name", type="string", description="имя контрагента", in="formData", default=""),
     *   @SWG\Parameter(name="account_id", type="integer", description="идентификатор ЛС", in="formData", default=""),
     *   @SWG\Response(response=200, description="данные о клиенте", @SWG\Schema(ref="#/definitions/get-full-client-struct")),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     */
    public function actionGetFullClientStruct()
    {
        $ids = $this->_getIdsForFullClientStruct();

        $fullResult = [];
        foreach ($ids as $id) {
            $super = ClientSuper::find()
                ->where(['id' => $id])
                ->with('contragents')
                ->with('contracts')
                ->with('accounts')
                ->one();

            $timezone = DateTimeZoneHelper::TIMEZONE_MOSCOW;

            $resultContragents = [];

            /** @var ClientContragent $contragent */
            foreach ($super->contragents as $contragent) {
                $resultContracts = [];
                /** @var ClientContract $contract */
                foreach ($contragent->contracts as $contract) {
                    $resultAccounts = [];
                    /** @var ClientAccount $account */
                    foreach ($contract->accounts as $account) {
                        $resultAccounts[] = [
                            'id' => $account->id,
                            'is_disabled' => $contract->business_process_status_id != BusinessProcessStatus::TELEKOM_MAINTENANCE_WORK,
                            'version' => $account->account_version,
                            'applications' => $this->_getPlatformaServicesCleaned($account->client)
                        ];
                        $timezone = $account->timezone_name;
                    }

                    if ($resultAccounts) {
                        $resultContracts[] = [
                            'id' => $contract->id,
                            'number' => $contract->number,
                            'state' => $contract->state,
                            'can_login_as_clients' => $contract->is_lk_access,
                            'partner_id' => $contract->isPartnerAgent(),
                            'is_partner' => $contract->isPartner(),
                            'partner_login_allow' => $contract->is_partner_login_allow,
                            'accounts' => $resultAccounts
                        ];
                    }
                }

                if ($resultContracts) {
                    $resultContragents[] = [
                        'id' => $contragent->id,
                        'name' => $contragent->name,
                        'country' => $contragent->country->alpha_3,
                        'contracts' => $resultContracts
                    ];
                }
            }

            if ($resultContragents) {
                $fullResult[$super->id] = [
                    'id' => $super->id,
                    'timezone' => $timezone,
                    'name' => $super->name,
                    'contragents' => $resultContragents
                ];
            }
        }

        return $fullResult;
    }

    /**
     * Возвращает массив super_client_id
     * (для функции get-full-client-struct)
     *
     * @return int[]
     */
    private function _getIdsForFullClientStruct()
    {
        $id = $name = $contract_id = $contragent_id = $contragent_name = $account_id = null;

        foreach (['id', 'name', 'contract_id', 'contragent_id', 'contragent_name', 'account_id'] as $var) {
            $$var = isset($this->requestData[$var]) ? $this->requestData[$var] : null;
        }

        if ($id) {
            return is_array($id) ? $id : [$id];
        }

        if ($name) {
            return array_keys(ClientSuper::find()
                ->andWhere(['name' => $name])
                ->indexBy('id')
                ->all());
        }

        if ($contragent_id) {
            $contragentIds = array_keys(
                ClientContragent::find()
                    ->where(['id' => $contragent_id])
                    ->indexBy('super_id')
                    ->all()
            );
            if ($contragentIds) {
                return $contragentIds;
            }
        }

        if ($contragent_name) {
            return array_keys(ClientContragent::find()
                ->andWhere(['name' => $contragent_name])
                ->indexBy('super_id')->all());
        }

        if ($contract_id) {
            $contractIds = array_keys(
                ClientContract::find()
                    ->where(['id' => $contract_id])
                    ->indexBy('super_id')
                    ->all()
            );
            if ($contractIds) {
                return $contractIds;
            }
        }

        if ($account_id) {
            $accountIds = array_keys(
                ClientAccount::find()
                    ->where(['id' => $account_id])
                    ->indexBy('super_id')
                    ->all()
            );
            if ($accountIds) {
                return $accountIds;
            }
        }

        return [];
    }

    /**
     * @param int $client
     * @return array
     * @throws \yii\db\Exception
     */
    private function _getPlatformaServices($client)
    {
        return array_map(function ($row) {
            $row['id'] = (int)$row['id'];
            $row['is_enabled'] = (bool)$row['enabled']; // TODO: Разобраться кто использует, и перевести с текстового "enabled" на булевое "is_enabled"
            return $row;
        },
            Yii::$app->db->createCommand("
                select 
                    `usage_voip`.`client` AS `client`,
                    `usage_voip`.`id` AS `id`,
                    'phone' AS `name`,
                    ((`usage_voip`.`actual_from` <= now()) and (`usage_voip`.`actual_to` >= now())) AS `enabled` 
                from `usage_voip`  
                where client = :client
                
                union all 
                select 
                    `usage_virtpbx`.`client` AS `client`,
                    `usage_virtpbx`.`id` AS `id`,
                    'vpbx' AS `name`,
                    ((`usage_virtpbx`.`actual_from` <= now()) and (`usage_virtpbx`.`actual_to` >= now())) AS `enabled` 
                from `usage_virtpbx`
                where client = :client
                  
                union all 
                
                select 
                    `usage_call_chat`.`client` AS `client`,
                    `usage_call_chat`.`id` AS `id`,
                    'feedback' AS `name`,
                    ((`usage_call_chat`.`actual_from` <= now()) and (`usage_call_chat`.`actual_to` >= now())) AS `enabled` 
                from `usage_call_chat`
                where client = :client
        ", [":client" => $client])->queryAll());
    }


    /**
     * Возвращает массив продуктов и их статус у клиента
     *
     * @param string $client
     * @return array
     * @throws \yii\db\Exception
     */
    private function _getPlatformaServicesCleaned($client)
    {
        return array_map(function ($row) {
            $row['id'] = (int)$row['id'];
            $row['is_enabled'] = (bool)$row['is_enabled'];
            return $row;
        },
            Yii::$app->db->createCommand("
                SELECT 
                    `usage_voip`.`id` AS `id`,
                    'phone' AS `name`,
                    (CAST(NOW() AS DATE) BETWEEN `actual_from` AND `actual_to`) AS `is_enabled` 
                FROM `usage_voip`  
                WHERE client = :client
                
                UNION ALL 
                SELECT 
                    `usage_virtpbx`.`id` AS `id`,
                    'vpbx' AS `name`,
                    (CAST(NOW() AS DATE) BETWEEN `actual_from` AND `actual_to`) AS `is_enabled` 
                FROM `usage_virtpbx`
                WHERE client = :client
                  
                UNION ALL 
                SELECT 
                    `usage_call_chat`.`id` AS `id`,
                    'feedback' AS `name`,
                    (CAST(NOW() AS DATE) BETWEEN `actual_from` AND `actual_to`) AS `is_enabled` 
                FROM `usage_call_chat`
                WHERE client = :client
        ", [":client" => $client])->queryAll());
    }

    /**
     * @SWG\Post(tags={"Работа с клиентами"}, path="/internal/client/create/", summary="Создание клиента", operationId="Создание клиента",
     *   @SWG\Parameter(name="company", type="string", description="Название клиента", in="formData", default="Клиент без названия"),
     *   @SWG\Parameter(name="address", type="string", description="Адрес", in="formData", default=""),
     *   @SWG\Parameter(name="partner_id", type="integer", description="ID партнёра", in="formData", default=""),
     *   @SWG\Parameter(name="contact_phone", type="string", description="Контактный телефон", in="formData", default=""),
     *   @SWG\Parameter(name="official_phone", type="string", description="Телефон организации", in="formData", default=""),
     *   @SWG\Parameter(name="fio", type="string", description="Ф.И.О. контактного лица", in="formData", default=""),
     *   @SWG\Parameter(name="fax", type="string", description="Факс", in="formData", default=""),
     *   @SWG\Parameter(name="email", type="string", description="Email", in="formData", default="", required=true),
     *   @SWG\Parameter(name="comment", type="string", description="Комментарий к заказу", in="formData", default=""),
     *   @SWG\Parameter(name="timezone", type="string", description="Таймзона лицевого счета", in="formData", default="Europe/Moscow"),
     *   @SWG\Parameter(name="country_id", type="integer", description="Код страны подключения (ISO)", in="formData", default="643"),
     *   @SWG\Parameter(name="site_name", type="string", description="С какого сайта пришел клиент", in="formData", default=""),
     *   @SWG\Parameter(name="vats_tariff_id", type="integer", description="ID тарифа для ВАТС", in="formData", default=""),
     *   @SWG\Parameter(name="account_version", type="integer", description="Версия биллера", in="formData", default="4"),
     *   @SWG\Parameter(name="entry_point_id", type="string", description="ID (code) точки входа", in="formData", default="RU1"),
     *
     *   @SWG\Response(response=200, description="данные о созданном клиенте",
     *     @SWG\Schema(type="object", required={"id","name","contragents"},
     *       @SWG\Property(property="client_id", type="integer", description="Идентификатор супер-клиента"),
     *       @SWG\Property(property="is_created", type="boolean", description="Создан ли клиент")
     *     )
     *   ),
     *   @SWG\Response(response="default", description="Ошибки",
     *     @SWG\Schema(ref="#/definitions/error_result")
     *   )
     * )
     */
    public function actionCreate()
    {
        $form = new ClientCreateExternalForm;
        $form->setAttributes($this->requestData);

        if ($form->validate()) {
            if ($form->create()) {
                $data = [
                    'client_id' => $form->super_id,
                    'is_created' => $form->isCreated,
                ];

                $account = ClientAccount::findOne(['id' => $form->account_id]);

                if ($account->contract->isPartner()) {
                    $data += [
                        'is_partner' => true,
                        'partner_id' => $account->id
                    ];
                }

                if ($account->contract->isPartnerAgent()) {
                    $contract = ClientContract::findOne(['id' => $account->contragent->partner_contract_id]);
                    $data += [
                        'is_partner_agent' => true,
                        'partner_id' => $contract->accounts[0]->id
                    ];
                }

                return $data;
            }
        } else {
            $fields = array_keys($form->errors);
            if ($fields[0] == 'partner_id') {
                throw new PartnerNotFoundException();
            } else {
                throw new Exception($form->errors[$fields[0]][0], 400);
            }
        }
    }

    /**
     * @SWG\Post(tags={"Работа с клиентами"}, path="/internal/client/set-timezone", summary="Устанавливаем таймзону клиента", operationId="Устанавливаем таймзону клиента",
     *   @SWG\Parameter(name="client_id", type="integer", description="ID (супер) клиента", in="formData", default=""),
     *   @SWG\Parameter(name="account_id", type="integer", description="ID лицевого счета", in="formData", default=""),
     *   @SWG\Parameter(name="timezone", type="string", enum={"Bad/Timezone", "Asia/Novosibirsk", "Asia/Vladivostok", "Asia/Yekaterinburg", "Europe/Budapest", "Europe/Moscow", "Europe/Samara", "Europe/Volgograd"}, description="Таймзона", in="formData", default="Europe/Moscow"),
     *   @SWG\Response(response=200, description="данные о созданном клиенте",
     *   ),
     *   @SWG\Response(response="default", description="Ошибки",
     *     @SWG\Schema(ref="#/definitions/error_result")
     *   )
     * )
     */
    public function actionSetTimezone()
    {
        $clientId = isset($this->requestData['client_id']) ? $this->requestData['client_id'] : null;
        $accountId = isset($this->requestData['account_id']) ? $this->requestData['account_id'] : null;
        $timezone = isset($this->requestData['timezone']) ? $this->requestData['timezone'] : null;

        if (!$clientId) {
            if ($accountId && $account = ClientAccount::findOne(['id' => $accountId])) {
                $clientId = $account->super_id;
            }
        }

        if (!$clientId) {
            throw new BadRequestHttpException('Клиент не найден');
        }

        $client = ClientSuper::findOne(['id' => $clientId]);

        if (!$client) {
            throw new BadRequestHttpException('Клиент не найден');
        }

        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            throw new BadRequestHttpException('Не найдена timezone');
        }

        ClientAccount::updateAll(['timezone_name' => $timezone], ['super_id' => $client->id]);

        return true;
    }
}
