<?php
namespace app\forms\client;

use Yii;
use DateTime;
use DateTimeZone;
use Exception;
use VoipReservNumber;
use app\classes\Form;
use app\models\Number;
use app\classes\StatModule;
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
use app\classes\validators\FormFieldValidator;

/**
 * Форма добавления клиента из внешнего мира.
 *
 **/

class ClientCreateExternalForm extends Form
{
    private $super_id = 0;
    private $contragent_id = 0;
    private $contract_id = 0;
    private $account_id = 0;

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


    public function rules()
    {
        $rules = [
            ['email', 'required'],
            [['company', 'fio', 'contact_phone', 'email', 'official_phone', 'fax', 'address', 'comment'], 'default', 'value' => ''],
            [['company', 'fio', 'contact_phone', 'email', 'official_phone', 'fax', 'address', 'comment'], FormFieldValidator::className()],
            ['company', 'default', 'value' => 'Клиент с сайта'],
            [['partner_id', 'vats_tariff_id'], 'default', 'value' => 0],
            [['partner_id'], 'integer'],
            [['partner_id'], 'validatePartnerId']

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
            'comment' => 'Комментарий'
        ];
    }

    public function validatePartnerId($attr, $params = [])
    {
        $partnerId = $this->$attr;
        
        if (!$partnerId) {
            return ;
        }

        if (
            ($account = ClientAccount::findOne(['id' => $partnerId]))
            && ($account->contract->business_id == Business::PARTNER)
        ) {
            // OK
        } else {
            $this->addError($attr, "Партнер не найден");
        }
    }

    public function findByEmail()
    {
        $c = ClientContact::findOne(['data' => $this->email, 'type' => 'email']);

        if ($c) {
            $this->account_id    = $c->client->id;
            $this->contract_id   = $c->client->contract->id;
            $this->contragent_id = $c->client->contract->contragent->id;
            $this->super_id      = $c->client->contract->contragent->super->id;

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
        $transaction = Yii::$app->db->beginTransaction();

        $retVast = [];

        if ($this->findByEmail()) {
            //
        } else {

            $this->createClientStruct();

            if ($this->account_id) {
                $this->createTroubleAndWizard();
                if ($this->vats_tariff_id) {
                    $resVats = $this->createVats();
                }
            }
        }
        $transaction->commit();


        $answer = "";
        if ($this->account_id) {
            $answer = "ok:".$this->account_id;

            if ($resVats) {
                $answer .= ":vats:" . $resVats['status'] . ":" . $resVats['info'];
            }
        } else {
            $answer = 'error:';
        }

        return $answer;
    }

    private function createClientStruct()
    {
        $s = new \app\models\ClientSuper();
        $s->name = $this->company;
        $s->validate();
        $s->save();
        Yii::info($s);
        $this->super_id = $s->id;

        $cg = new \app\forms\client\ContragentEditForm(['super_id' => $s->id]);
        $cg->name = $cg->name_full = $this->company;
        $cg->address_jur = $this->address;
        $cg->legal_type = 'legal';
        $cg->validate();
        $cg->save();
        $this->contragent_id = $cg->id;
        Yii::info($cg);

        $cr = new \app\forms\client\ContractEditForm(['contragent_id' => $cg->id]);
        $cr->business_id = Business::TELEKOM;
        $cr->business_process_id = \app\models\BusinessProcess::TELECOM_SUPPORT;
        $cr->business_process_status_id = BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES;
        $cr->organization_id = Organization::MCN_TELEKOM;
        $cr->validate();
        $cr->save();
        $this->contract_id = $cr->id;
        Yii::info($cr);

        $ca = new \app\forms\client\AccountEditForm(['id' => $cr->newClient->id]);
        $ca->address_post = $this->address;
        $ca->address_post_real = $this->address;
        $ca->address_connect = $this->address;
        $ca->status = "income";
        $ca->validate();

        $ca->save();
        $this->account_id = $ca->id;

        Yii::info($ca);
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
        $R = array(
            'trouble_type' => 'connect',
            'trouble_subtype' => 'connect',
            'client' => "id".$this->account_id,
            'date_start' => date('Y-m-d H:i:s'),
            'date_finish_desired' => date('Y-m-d H:i:s'),
            'problem' => "Входящие клиент с сайта: ".$this->company,
            'user_author' => "system",
            'first_comment' => $this->comment
        );

        $troubleId = StatModule::tt()->createTrouble($R, "system");
        LkWizardState::create($this->contract_id, $troubleId);

        return true;
    }

    public function createVats()
    {
        $result = ['status' => 'error'];

        $client = ClientAccount::findOne(['id' => $this->account_id]);
        $tarif = TariffVirtpbx::findOne([['id' => $this->vats_tariff_id], ['!=', 'status', 'archive']]);

        if ($client && $tarif)
        {
            $actual_from = date('Y-m-d');
            $actual_to = '4000-01-01';

            $vats = new UsageVirtpbx;
            $vats->client = $client->client;
            $vats->activation_dt = (new DateTime($actual_from, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $vats->expire_dt = (new DateTime($actual_to, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $vats->actual_from = $actual_from;
            $vats->actual_to = $actual_to;
            $vats->amount = 1;
            $vats->status = 'connecting';
            $vats->region = \app\models\Region::MOSCOW;
            $vats->save();

            $logTarif = new LogTarif;
            $logTarif->service = 'usage_virtpbx';
            $logTarif->id_service = $vats->id;
            $logTarif->id_tarif = $tarif->id;
            $logTarif->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $logTarif->date_activation = date('Y-m-d');
            $logTarif->id_user = User::LK_USER_ID;
            $logTarif->save();

            $result['status'] = 'ok';
            $result['info'][] = 'created';

            if ($tarif->id == TariffVirtpbx::TEST_TARIFF_ID) {
                $usage = UsageVoip::findOne(['client' => $client->client]);

                if (!($usage instanceof UsageVoip)) {
                    $result['info'][] = 'voip';

                    $freeNumber = Number::dao()->getRandomFreeNumber(DidGroup::MOSCOW_STANDART_GROUP_ID);

                    if (!($freeNumber instanceof Number)) {
                        throw new Exception('Not found free number into 499 DID group', 500);
                    }

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $form = new UsageVoipEditForm;
                        $form->scenario = 'add';
                        $form->initModel($client);
                        $form->did = $freeNumber->number;
                        $form->prepareAdd();
                        $form->tariff_main_id = VoipReservNumber::getDefaultTarifId($client->region, $client->currency);
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

                        $usageVoipId = $form->id;

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
