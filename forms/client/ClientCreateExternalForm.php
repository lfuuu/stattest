<?php

namespace app\forms\client;

use app\classes\Form;
use app\classes\StatModule;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\forms\comment\ClientContractCommentForm;
use app\forms\usage\UsageVoipEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;
use app\models\City;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientSuper;
use app\models\Country;
use app\models\DidGroup;
use app\models\EntryPoint;
use app\models\filter\FreeNumberFilter;
use app\models\Lead;
use app\models\LkWizardState;
use app\models\LogTarif;
use app\models\Organization;
use app\models\Region;
use app\models\TariffVirtpbx;
use app\models\Timezone;
use app\models\Trouble;
use app\models\TroubleRoistatStore;
use app\models\usages\UsageInterface;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\User;
use app\modules\nnp\models\NdcType;
use DateTime;
use DateTimeZone;
use Exception;
use VoipReserveNumber;
use Yii;
use yii\helpers\Json;

/**
 * Форма добавления клиента из внешнего мира.
 */
class ClientCreateExternalForm extends Form
{
    private $_semaforId = 777;
    private $_semaforResource = null;

    public
        $super_id = 0,
        $contragent_id = 0,
        $contract_id = 0,
        $account_id = 0,

        $vats_tariff_id = 0,

        $email,
        $company,
        $fio,
        $contact_phone,
        $official_phone,
        $fax,
        $address,
        $comment,
        $partner_id,
        $partner_contract_id = 0,

        $timezone,
        $country_id,
        $site_name,

        $info = '',
        $isCreated = null,

        $ip = '',
        $connect_region = Region::MOSCOW,
        $account_version = ClientAccount::DEFAULT_ACCOUNT_VERSION,

        $entry_point_id = '',

        $org_type = '',

        $utm_parameters = [],

        $troubleId = null,

        $roistat_visit = null,

        $is_create_lk = 1,

        $is_create_trouble = true;

    /** @var EntryPoint */
    public $entryPoint = null;

    /**
     * ClientCreateExternalForm constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->_semaforResource = sem_get($this->_semaforId);

        if ($this->_semaforResource) {
            sem_acquire($this->_semaforResource);
        }

        parent::__construct($config);
    }

    /**
     * ClientCreateExternalForm destructor.
     */
    public function __destruct()
    {
        if ($this->_semaforResource) {
            sem_release($this->_semaforResource);
        }
    }

    /**
     * Правила модели
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['email', 'required'],
            ['email', 'email'],
            [
                [
                    'company',
                    'fio',
                    'contact_phone',
                    'email',
                    'official_phone',
                    'fax',
                    'address',
                    'comment',
                    'site_name',
                    'org_type',
                ],
                'default',
                'value' => ''
            ],
            [
                [
                    'company',
                    'fio',
                    'contact_phone',
                    'email',
                    'official_phone',
                    'fax',
                    'address',
                    'comment',
                    'site_name',
                    'entry_point_id',
                    'org_type',
                ],
                FormFieldValidator::class
            ],
            [['partner_id', 'vats_tariff_id'], 'default', 'value' => 0],
            [['is_create_lk', 'is_create_trouble'], 'default', 'value' => 1],
            [['partner_id', 'is_create_lk', 'is_create_trouble'], 'integer'],
            [['partner_id'], 'validatePartnerId'],
            ['timezone', 'in', 'range' => (array_keys(Timezone::getList()) + [""])],
            ['country_id', 'default', 'value' => Country::RUSSIA],
            ['country_id', 'in', 'range' => array_keys(Country::getList())],
            ['connect_region', 'default', 'value' => Region::MOSCOW],
            [['ip', 'utm_parameters'], 'safe'],
            [['roistat_visit',], 'integer'],
        ];
        return $rules;
    }

    /**
     * Название полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'company' => 'Название компании',
            'fio' => 'ФИО',
            'contact_phone' => 'Контактный телефон',
            'official_phone' => 'Официальный телефон',
            'email' => 'E-mail',
            'address' => 'Адрес',
            'comment' => 'Комментарий',
            'timezone' => 'Временная зона',
            'country_id' => 'Код страны',
            'site_name' => 'Сайт',
            'roistat_visit' => 'Roistat visit',
            'org_type' => 'Тип организации',
        ];
    }

    /**
     * Валидация id партнера
     *
     * @param string $attr
     */
    public function validatePartnerId($attr)
    {
        $partnerId = $this->{$attr};

        if (!$partnerId) {
            return;
        }

        if (
        !(
            ($account = ClientAccount::findOne(['id' => $partnerId]))
            && ($account->contract->isPartner())
        )
        ) {
            $this->addError($attr, "Партнер не найден");
            return;
        }

        $this->partner_contract_id = $account->contract_id;
    }

    /**
     * Сохранение формы. Сохранения реализовано другими функциями
     */
    public function save()
    {
        // nothing
    }

    /**
     * Создание клиента
     *
     * @return bool|null
     * @throws \yii\db\Exception
     */
    public function create()
    {
        $resVats = null;

        $this->entryPoint = EntryPoint::getByIdOrDefault($this->entry_point_id);
        if (!$this->timezone) {
            $this->timezone = $this->entryPoint->timezone_name;
        }

        if (!$this->timezone) {
            $this->timezone = DateTimeZoneHelper::TIMEZONE_MOSCOW;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->_createClientStruct();
            $this->isCreated = true;

            if ($this->account_id && $this->is_create_trouble) {
                $this->_createTroubleAndWizard();
                if ($this->vats_tariff_id) {
                    $resVats = $this->_createVats();
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            Yii::error($e);
            $transaction->rollBack();

//            throw $e;
            return false;
        }

        $result = null;
        if ($this->account_id) {
            $result = true;

            if ($resVats) {
                $this->info = "vats:" . $resVats['status'] . ":" . $resVats['info'];
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Создание структуры клиента
     *
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\InvalidParamException
     */
    private function _createClientStruct()
    {
        $super = new ClientSuper();
        $super->name = $this->company ?: 'Client #';
        if ($this->utm_parameters) {
            $super->utm = is_array($this->utm_parameters) ? json_encode(array_filter($this->utm_parameters)) : $this->utm_parameters;
        }

        if (!$this->is_create_lk) {
            $super->detachBehavior('CheckCreateCoreAdmin');
        }

        $super->validate();
        if (!$super->save()) {
            throw new ModelValidationException($super);
        }

        Yii::info($super);
        $this->super_id = $super->id;
        $this->entryPoint && $super->entry_point_id = $this->entryPoint->id;

        $super->name = ($this->entryPoint ? $this->entryPoint->name_prefix : $super->name);

        if (!$super->save()) {
            throw new ModelValidationException($super);
        }

        $contragent = new ContragentEditForm(['super_id' => $super->id]);
        $contragent->name = $contragent->name_full = $this->company ?: $super->name;
        $contragent->address_jur = $this->address;
        $contragent->legal_type = 'legal';
        $contragent->country_id = $this->country_id;
        $contragent->org_type = $this->org_type;

        if ($this->entryPoint) {
            $contragent->country_id = $this->entryPoint->country_id;
            $contragent->lang_code = $this->entryPoint->country->lang;
            if ($this->entryPoint->org_type) {
                $contragent->org_type = $this->entryPoint->org_type;
            }
        }

        if (!$contragent->validate() || !$contragent->save()) {
            throw new ModelValidationException($contragent);
        }

        $this->contragent_id = $contragent->id;
        Yii::info($contragent);


        // Установка партнера
        $partnerContractId = null;
        if ($this->partner_contract_id) {
            $partnerContractId = $this->partner_contract_id;
        } elseif (isset($this->utm_parameters['utm_referral_id'])) {
            $partnerAccount = ClientAccount::findOne(['id' => $this->utm_parameters['utm_referral_id']]);
            if (($partnerContract = $partnerAccount->contract) && $partnerContract->isPartner()) {
                $partnerContractId = $partnerContract->id;
            }
        }

        $contract = new ContractEditForm([
            'contragent_id' => $contragent->id,
            'partner_contract_id' => $partnerContractId,
        ]);

        if ($this->entryPoint) {
            $contract->business_id = $this->entryPoint->client_contract_business_id;
            $contract->business_process_id = $this->entryPoint->client_contract_business_process_id;
            $contract->business_process_status_id = $this->entryPoint->client_contract_business_process_status_id;
            $contract->organization_id = $this->entryPoint->organization_id;
        } else {
            $contract->business_id = Business::TELEKOM;
            $contract->business_process_id = BusinessProcess::TELECOM_SUPPORT;
            $contract->business_process_status_id = BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES;
            $contract->organization_id = Organization::MCN_TELECOM;
        }

        if (!$contract->validate() || !$contract->save()) {
            throw new ModelValidationException($contract);
        }

        $this->contract_id = $contract->id;
        Yii::info($contract);

        $account = new AccountEditForm(['id' => $contract->newClient->id]);
        $account->address_post = $this->address;
        $account->address_post_real = $this->address;
        $account->address_connect = $this->address;
        $account->status = "income";
        $account->timezone_name = $this->timezone;
        $account->site_name = $this->site_name;
        $account->account_version = $this->account_version;

        if ($this->entryPoint) {
            $account->region = $this->entryPoint->region_id;
            $account->currency = $this->entryPoint->currency_id;
            $account->is_postpaid = $this->entryPoint->is_postpaid;
            $account->account_version = $this->entryPoint->account_version;
            $account->price_level = $this->entryPoint->price_level;
            $account->credit = $this->entryPoint->credit;
            $account->voip_credit_limit_day = $this->entryPoint->voip_credit_limit_day;
            $account->voip_limit_mn_day = $this->entryPoint->voip_limit_mn_day;
            $account->status = BusinessProcessStatus::find()
                ->where(['id' => $this->entryPoint->client_contract_business_process_status_id])
                ->select('oldstatus')
                ->scalar();

            if ($this->entryPoint->name) {
                $comment = new ClientContractCommentForm();
                $comment->contract_id = $this->contract_id;
                $comment->comment = $this->entryPoint->name;
                if (!$comment->save()) {
                    throw new ModelValidationException($comment);
                }
            }
        }

        if (!$account->validate() || !$account->save()) {
            throw new ModelValidationException($account);
        }

        $this->account_id = $account->id;

        Yii::info($account);
        if ($this->contact_phone) {
            $this->_addContact([
                'type' => 'phone',
                'data' => $this->contact_phone,
                'comment' => $this->fio,
            ]);
        }

        if ($this->official_phone) {
            $this->_addContact([
                'type' => 'phone',
                'data' => $this->official_phone,
                'comment' => $this->fio,
                'is_official' => 1,
            ]);
        }

        if ($this->fax) {
            $this->_addContact([
                'type' => 'fax',
                'data' => $this->fax,
                'comment' => $this->fio,
                'is_official' => 1
            ]);
        }

        if ($this->email) {
            $this->_addContact([
                'type' => 'email',
                'data' => $this->email,
                'comment' => $this->fio,
                'is_official' => 1
            ]);
        }
    }

    /**
     * Добавление контакта
     *
     * @param array $attrs
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\InvalidParamException
     */
    private function _addContact($attrs = [])
    {
        $clientContact = new ClientContact();
        $clientContact->setAttributes(array_merge([
                'client_id' => $this->account_id,
                'user_id' => User::CLIENT_USER_ID,
                'is_official' => 0
            ],
                $attrs)
        );

        if (!$clientContact->validate()) {
            // не распознано. Ошибку нельзя давать, надо по-любому сохранить
            $clientContact->is_validate = 0;
            if (!$clientContact->data) {
                $clientContact->data = '.'; // хоть что-нибудь, чтобы не падало
            }
        }

        if (!$clientContact->save()) {
            throw new ModelValidationException($clientContact);
        }
    }

    /**
     * Создание заявки и визарда
     *
     * @return bool
     * @throws ModelValidationException
     */
    private function _createTroubleAndWizard()
    {
        // Если у клиента есть созданный запись в таблице lead с trouble_id, то не нужно создавать Заявку от лица системы
        $this->troubleId = Lead::find()
            ->select('trouble_id')
            ->where(['account_id' => $this->account_id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->scalar();

        if (!$this->troubleId) {
            $R = [
                'trouble_type' => 'connect',
                'trouble_subtype' => 'connect',
                'client' => 'id' . $this->account_id,
                'date_start' => date(DateTimeZoneHelper::DATETIME_FORMAT),
                'date_finish_desired' => date(DateTimeZoneHelper::DATETIME_FORMAT),
                'problem' => 'Входящие клиент с сайта' . ($this->site_name ? ' ' . $this->site_name : '') . ': ' . $this->company,
                'user_author' => 'system',
                'first_comment' => $this->comment . PHP_EOL .
                    ($this->site_name ? "Клиент с сайта: " . $this->site_name . PHP_EOL : '') .
                    ($this->ip ? "IP-адрес: " . $this->ip . PHP_EOL : '') .
                    ($this->roistat_visit ? 'Roistat visit: ' . $this->roistat_visit . PHP_EOL : '') .
                    ($this->utm_parameters ? 'UTM: ' . json_encode($this->utm_parameters) . PHP_EOL : '')
                    ,
            ];

            $this->troubleId = StatModule::tt()->createTrouble($R, $this->entryPoint->connectTroubleUser->user);

            if ($this->roistat_visit) {
                $store = new TroubleRoistatStore();
                $store->account_id = $this->account_id;
                $store->roistat_visit = $this->roistat_visit;

                if (!$store->save()) {
                    throw new ModelValidationException($store);
                }
            }
        }

        LkWizardState::create(
            $this->contract_id,
            $this->troubleId,
            $this->entryPoint && $this->entryPoint->wizard_type ? $this->entryPoint->wizard_type : LkWizardState::TYPE_RUSSIA
        );

        return true;
    }

    /**
     * Создание ВАТС
     *
     * @return array
     * @throws Exception
     */
    private function _createVats()
    {
        $result = ['status' => 'error'];

        $client = ClientAccount::findOne(['id' => $this->account_id]);
        $tariff = TariffVirtpbx::findOne(['id' => $this->vats_tariff_id]);
        $testTariff = TariffVirtpbx::findOne(['status' => TariffVirtpbx::STATUS_TEST]);

        $vats = UsageVirtpbx::findOne(['client' => $client->client]);

        if (!$vats) {
            if ($client && $testTariff && $tariff) {
                $actual_from = date(DateTimeZoneHelper::DATE_FORMAT);
                $actual_to = UsageInterface::MAX_POSSIBLE_DATE;

                $vats = new UsageVirtpbx;
                $vats->client = $client->client;
                $vats->actual_from = $actual_from;
                $vats->actual_to = $actual_to;
                $vats->amount = 1;
                $vats->status = 'connecting';
                $vats->region = $this->connect_region;
                if (!$vats->save()) {
                    throw new ModelValidationException($vats);
                }

                $logTariff = new LogTarif;
                $logTariff->service = 'usage_virtpbx';
                $logTariff->id_service = $vats->id;
                $logTariff->id_tarif = $testTariff->id;
                $logTariff->ts = (new DateTime())->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))->format(DateTimeZoneHelper::DATETIME_FORMAT);
                $logTariff->date_activation = date(DateTimeZoneHelper::DATE_FORMAT);
                $logTariff->id_user = User::LK_USER_ID;
                if (!$logTariff->save()) {
                    throw new ModelValidationException($logTariff);
                }

                $result['status'] = 'ok';
                $result['info'][] = 'created';

                if ($this->troubleId && ($trouble = Trouble::findOne(['id' => $this->troubleId]))) {
                    $trouble->problem .= " Тариф ВАТС: " . $tariff->description . " (id:" . $tariff->id . ")";
                    if (!$trouble->save()) {
                        throw new ModelValidationException($trouble);
                    }
                }

                $this->_addUsageVoip($client, $vats, $result);
            } else {
                $result['info'][] = 'not_found_tariff';
            }
        }

        $result['info'] = implode(':', $result['info']);

        return $result;
    }

    /**
     * Добавление случайного номера в ВАТС
     *
     * @param ClientAccount $client
     * @param UsageVirtpbx $vats
     * @param array $result
     * @throws Exception
     */
    private function _addUsageVoip(ClientAccount $client, UsageVirtpbx $vats, &$result)
    {
        $usage = UsageVoip::findOne(['client' => $client->client]);

        if ($usage) {
            return;
        }

        $result['info'][] = 'voip';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $form = new UsageVoipEditForm;
            $form->scenario = 'add';
            $form->initModel($client);
            if ($this->connect_region == Region::HUNGARY) { // в венгрии подключаем только линии без номера
                $form->ndc_type_id = NdcType::ID_MCN_LINE;
                $form->city_id = City::DEFAULT_USER_CITY_ID;
            } else {
                $freeNumber
                    = (new FreeNumberFilter)
                    ->setIsService(false)
                    ->setNdcType(NdcType::ID_GEOGRAPHIC)
                    ->setCountry(Country::RUSSIA)
                    ->setCity(City::MOSCOW)
                    ->setDidGroup(DidGroup::ID_MOSCOW_STANDART_499)
                    ->randomOne();

                if (!$freeNumber) {
                    throw new \LogicException('Not found free number into 499 DID group', 500);
                }

                $form->did = $freeNumber->number;
            }

            $form->prepareAdd();
            $form->tariff_main_id = VoipReserveNumber::getDefaultTariffId(
                $client->region,
                $client->currency
            );
            $form->create_params = Json::encode([
                'vpbx_stat_product_id' => $vats->id,
            ]);

            if (!$form->validate() || !$form->add()) {
                throw new ModelValidationException($form, 500);
            }

            $transaction->commit();
            $result['info'][] = 'added';
        } catch (\Exception $e) {
            $result['info'][] = 'failed';
            $transaction->rollBack();
            throw $e;
        }
    }
}
