<?php
namespace app\forms\client;

use app\forms\comment\ClientContractCommentForm;
use app\models\City;
use app\models\Country;
use app\models\EntryPoint;
use app\models\Region;
use Yii;
use DateTime;
use DateTimeZone;
use Exception;
use VoipReserveNumber;
use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\classes\StatModule;
use app\models\Number;
use app\models\LkWizardState;
use app\models\Business;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\TariffVirtpbx;
use app\models\User;
use app\models\Organization;
use app\forms\usage\UsageVoipEditForm;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\DidGroup;
use app\models\ClientContact;
use app\models\LogTarif;
use app\models\usages\UsageInterface;
use app\helpers\DateTimeZoneHelper;

/**
 * Форма добавления клиента из внешнего мира.
 *
 **/
class ClientCreateExternalForm extends Form
{
    public $super_id = 0;
    public $contragent_id = 0;
    public $contract_id = 0;
    public $account_id = 0;

    public $vats_tariff_id = 0;

    public $email;
    public $company;
    public $fio;
    public $contact_phone;
    public $official_phone;
    public $fax;
    public $address;
    public $comment;
    public $partner_id;

    public $timezone;
    public $country_id;
    public $site_name;

    public $info = "";
    public $isCreated = null;

    public $ip = "";
    public $connect_region = Region::MOSCOW;
    public $account_version = "";

    public $entry_point_id = "";

    /** @var EntryPoint $entryPoint */
    private $entryPoint = null;


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
                    'site_name'
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
                ],
                FormFieldValidator::className()
            ],
            ['company', 'default', 'value' => 'Клиент без названия'],
            [['partner_id', 'vats_tariff_id'], 'default', 'value' => 0],
            [['partner_id', 'account_version'], 'integer'],
            [['partner_id'], 'validatePartnerId'],
            //['timezone', 'default', 'value' => Region::TIMEZONE_MOSCOW],
            ['timezone', 'in', 'range' => Region::getTimezoneList() + [""]],
            ['country_id', 'default', 'value' => Country::RUSSIA],
            ['country_id', 'in', 'range' => array_keys(Country::getList())],
            ['connect_region', 'default', 'value' => Region::MOSCOW],
            ['account_version', 'default', 'value' => ClientAccount::VERSION_BILLER_USAGE],
            ['ip', 'safe']

        ];
        return $rules;
    }

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
            'account_version' => 'Версия биллера',
        ];
    }

    public function validatePartnerId($attr, $params = [])
    {
        $partnerId = $this->$attr;

        if (!$partnerId) {
            return;
        }

        if (
        !(($account = ClientAccount::findOne(['id' => $partnerId])) && ($account->isPartner()))
        ) {
            $this->addError($attr, "Партнер не найден");
        }
    }

    public function findByEmail()
    {
        $c = ClientContact::findOne(['data' => $this->email, 'type' => 'email']);

        if ($c) {
            $this->account_id = $c->client->id;
            $this->contract_id = $c->client->contract->id;
            $this->contragent_id = $c->client->contragent->id;
            $this->super_id = $c->client->superClient->id;

            return true;
        }

        return false;
    }

    public function save()
    {
        //
    }

    public function create()
    {
        $resVats = null;

        $this->entryPoint = EntryPoint::getByIdOrDefault($this->entry_point_id);
        if (!$this->timezone) {
            $this->timezone = $this->entryPoint->timezone_name;
        }

        if (!$this->timezone) {
            $this->timezone = Region::TIMEZONE_MOSCOW;
        }

        $transaction = Yii::$app->db->beginTransaction();

        if ($this->findByEmail()) {
            $this->isCreated = false;
        } else {
            $this->createClientStruct();

            $this->isCreated = true;

            if ($this->account_id) {
                $this->createTroubleAndWizard();
            }
        }

        if ($this->account_id) {
            if ($this->vats_tariff_id) {
                $resVats = $this->createVats();
            }
        }

        $transaction->commit();

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

    private function createClientStruct()
    {
        $super = new \app\models\ClientSuper();
        $super->name = $this->company;
        $super->validate();
        $super->save();
        Yii::info($super);
        $this->super_id = $super->id;

        if ($this->entryPoint) {
            $super->name = $this->entryPoint->super_client_prefix . $super->id;
            $super->save();
        }

        $contragent = new \app\forms\client\ContragentEditForm(['super_id' => $super->id]);
        $contragent->name = $contragent->name_full = $this->company;
        $contragent->address_jur = $this->address;
        $contragent->legal_type = 'legal';
        $contragent->country_id = $this->country_id;
        if ($this->partner_id) {
            $contragent->partner_contract_id = $this->partner_id;
        }

        if ($this->entryPoint) {
            $contragent->country_id = $this->entryPoint->country_id;
        }

        $contragent->validate();
        $contragent->save();
        $this->contragent_id = $contragent->id;
        Yii::info($contragent);

        $contract = new \app\forms\client\ContractEditForm(['contragent_id' => $contragent->id]);

        if ($this->entryPoint) {
            $contract->business_id = $this->entryPoint->client_contract_business_id;
            $contract->business_process_id = $this->entryPoint->client_contract_business_process_id;
            $contract->business_process_status_id = $this->entryPoint->client_contract_business_process_status_id;
            $contract->organization_id = $this->entryPoint->organization_id;
        } else {
            $contract->business_id = Business::TELEKOM;
            $contract->business_process_id = \app\models\BusinessProcess::TELECOM_SUPPORT;
            $contract->business_process_status_id = BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES;
            $contract->organization_id = Organization::MCN_TELEKOM;
        }

        $contract->validate();
        $contract->save();
        $this->contract_id = $contract->id;
        Yii::info($contract);

        $account = new \app\forms\client\AccountEditForm(['id' => $contract->newClient->id]);
        $account->address_post = $this->address;
        $account->address_post_real = $this->address;
        $account->address_connect = $this->address;
        $account->status = "income";
        $account->timezone_name = $this->timezone;
        $account->site_name = $this->site_name;
        $account->account_version = $this->account_version;

        if ($this->entryPoint) {
            $account->currency = $this->entryPoint->currency_id;
            $account->is_postpaid = $this->entryPoint->is_postpaid;
            $account->account_version = $this->entryPoint->account_version;
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
                $comment->save();
            }
        }

        $account->validate();

        $account->save();
        $this->account_id = $account->id;

        Yii::info($account);
        if ($this->contact_phone) {
            $this->addContact([
                'type' => 'phone',
                'data' => $this->contact_phone,
                'comment' => $this->fio,
            ]);
        }

        if ($this->official_phone) {
            $this->addContact([
                'type' => 'phone',
                'data' => $this->official_phone,
                'comment' => $this->fio,
                'is_official' => 1,
            ]);
        }

        if ($this->fax) {
            $this->addContact([
                'type' => 'fax',
                'data' => $this->fax,
                'comment' => $this->fio,
                'is_official' => 1
            ]);
        }

        $this->addContact([
            'type' => 'email',
            'data' => $this->email,
            'comment' => $this->fio,
            'is_official' => 1
        ]);
    }

    private function createTroubleAndWizard()
    {
        $R = [
            'trouble_type' => 'connect',
            'trouble_subtype' => 'connect',
            'client' => "id" . $this->account_id,
            'date_start' => date(DateTimeZoneHelper::DATETIME_FORMAT),
            'date_finish_desired' => date(DateTimeZoneHelper::DATETIME_FORMAT),
            'problem' => "Входящие клиент с сайта" . ($this->site_name ? ' ' . $this->site_name : '') . ": " . $this->company,
            'user_author' => "system",
            'first_comment' => $this->comment . ($this->site_name ? "\nКлиент с сайта: " . $this->site_name : '') . ($this->ip ? "\nIP-адрес: " . $this->ip : '')
        ];

        $troubleId = StatModule::tt()->createTrouble($R, "system");

        if ($this->entryPoint) {
            LkWizardState::create($this->contract_id, $troubleId, $this->entryPoint->wizard_type);
        } else {
            LkWizardState::create($this->contract_id, $troubleId);
        }

        return true;
    }

    public function createVats()
    {
        $result = ['status' => 'error'];

        $client = ClientAccount::findOne(['id' => $this->account_id]);
        $tarif = TariffVirtpbx::findOne([['id' => $this->vats_tariff_id], ['!=', 'status', 'archive']]);

        $vats = UsageVirtpbx::findOne(['client' => $client->client]);

        if (!$vats) {
            if ($client && $tarif) {
                $actual_from = date(DateTimeZoneHelper::DATE_FORMAT);
                $actual_to = UsageInterface::MAX_POSSIBLE_DATE;

                $vats = new UsageVirtpbx;
                $vats->client = $client->client;
                $vats->actual_from = $actual_from;
                $vats->actual_to = $actual_to;
                $vats->amount = 1;
                $vats->status = 'connecting';
                $vats->region = $this->connect_region;
                $vats->save();

                $logTarif = new LogTarif;
                $logTarif->service = 'usage_virtpbx';
                $logTarif->id_service = $vats->id;
                $logTarif->id_tarif = $tarif->id;
                $logTarif->ts = (new DateTime())->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))->format(DateTimeZoneHelper::DATETIME_FORMAT);
                $logTarif->date_activation = date(DateTimeZoneHelper::DATE_FORMAT);
                $logTarif->id_user = User::LK_USER_ID;
                $logTarif->save();

                $result['status'] = 'ok';
                $result['info'][] = 'created';

                if ($tarif->id == TariffVirtpbx::TEST_TARIFF_ID) {
                    $usage = UsageVoip::findOne(['client' => $client->client]);

                    if (!($usage instanceof UsageVoip)) {
                        $result['info'][] = 'voip';

                        $transaction = Yii::$app->db->beginTransaction();
                        try {
                            $form = new UsageVoipEditForm;
                            $form->scenario = 'add';
                            $form->initModel($client);
                            if ($this->connect_region == Region::HUNGARY) { //в венгрии подключаем только линии без номера
                                $form->type_id = 'line';
                                $form->city_id = City::DEFAULT_USER_CITY_ID;
                            } else {
                                $freeNumber =
                                    (new \app\models\filter\FreeNumberFilter)
                                        ->getNumbers()
                                        ->setDidGroup(DidGroup::MOSCOW_STANDART_GROUP_ID)
                                        ->randomOne();

                                if (!($freeNumber instanceof Number)) {
                                    throw new Exception('Not found free number into 499 DID group', 500);
                                }

                                $form->did = $freeNumber->number;
                            }
                            $form->prepareAdd();
                            $form->tariff_main_id = VoipReserveNumber::getDefaultTariffId(
                                $client->region,
                                $client->currency
                            );
                            $form->create_params = \yii\helpers\Json::encode([
                                'vpbx_stat_product_id' => $vats->id,
                            ]);

                            if (!$form->validate() || !$form->add()) {
                                if ($form->errors) {
                                    \Yii::error($form);
                                    $errorKeys = array_keys($form->errors);
                                    throw new Exception($form->errors[$errorKeys[0]][0], 500);
                                } else {
                                    throw new Exception('Unknown error', 500);
                                }
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
            } else {
                $result['info'][] = 'not_found_tariff';
            }
        }

        $result['info'] = implode(':', $result['info']);

        return $result;
    }

    private function addContact($attrs = [])
    {
        $c = new ClientContact();
        $c->setAttributes(array_merge([
            'client_id' => $this->account_id,
            'user_id' => User::CLIENT_USER_ID,
            'is_active' => 1,
            'is_official' => 0
        ], $attrs));
        $c->save();
    }
}
