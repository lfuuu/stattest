<?php
namespace app\forms\client;

use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Currency;
use app\models\HistoryVersion;
use app\models\PriceType;
use app\models\Region;
use Yii;
use app\classes\Form;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use app\models\Bik;
use app\classes\validators\BikValidator;

class AccountEditForm extends Form
{
    protected $clientM = null;
    public $historyVersionRequestedDate = null;
    public $historyVersionStoredDate = null;

    public $id,
        $super_id,
        $contract_id;

    public
        $client,
        $region = ClientAccount::DEFAULT_REGION,
        $status,
        $address_post,
        $address_post_real,
        $address_connect,
        $currency,
        $stamp,
        $nal,
        $credit = ClientAccount::DEFAULT_CREDIT,
        $phone_connect,
        $form_type,
        $price_type,
        $voip_credit_limit,
        $voip_disabled,
        $voip_credit_limit_day = ClientAccount::DEFAULT_VOIP_CREDIT_LIMIT_DAY,
        $voip_is_day_calc = ClientAccount::DEFAULT_VOIP_IS_DAY_CALC,
        $mail_print,
        $mail_who,
        $head_company,
        $head_company_address_jur,
        $bill_rename1 = 'no',
        $is_agent,
        $is_with_consignee,
        $consignee,
        $is_upd_without_sign,
        $timezone_name = Region::TIMEZONE_MOSCOW,
        $is_active,
        $admin_contact_id = 0,
        $admin_is_active = 0,
        $bik,
        $corr_acc,
        $pay_acc,
        $bank_name,
        $custom_properties,
        $bank_properties,
        $bank_city,
        $admin_email,
        $lk_balance_view_mode;

    public function rules()
    {
        $rules = [
            [
                [
                    'client', 'address_post', 'address_post_real', 'address_connect', 'phone_connect',
                    'mail_who', 'head_company', 'head_company_address_jur', 'consignee',
                    'bik','corr_acc','pay_acc','bank_name','bank_city', 'bank_properties',
                    'historyVersionStoredDate',
                ],
                'string'
            ],
            [
                [
                    'client', 'address_post', 'address_post_real', 'address_connect', 'phone_connect',
                    'mail_who', 'head_company', 'head_company_address_jur', 'consignee',
                    'bik','corr_acc','pay_acc','bank_name','bank_city', 'bank_properties', 'admin_email'
                ],
                'default', 'value' => ''
            ],
            [
                [
                    'id', 'super_id', 'contract_id', 'stamp', 'credit', 'voip_credit_limit',
                    'voip_disabled', 'voip_credit_limit_day', 'voip_is_day_calc', 'is_with_consignee', 'is_upd_without_sign',
                    'is_agent', 'mail_print', 'admin_contact_id', 'admin_is_active'
                ],
                'integer'
            ],
            [
                [
                    'stamp', 'credit', 'voip_credit_limit', 'is_agent', 'mail_print',
                    'voip_disabled', 'voip_credit_limit_day', 'is_with_consignee', 'is_upd_without_sign',
                ],
                'default', 'value' => 0
            ],
            [['voip_credit_limit_day'], 'default', 'value' => ClientAccount::DEFAULT_VOIP_CREDIT_LIMIT_DAY],
            ['admin_email', 'email'],
            ['credit', 'integer', 'min' => 0],
            ['voip_is_day_calc', 'default', 'value' => ClientAccount::DEFAULT_VOIP_IS_DAY_CALC],
            ['currency', 'in', 'range' => array_keys(Currency::map())],
            ['form_type', 'in', 'range' => array_keys(ClientAccount::$formTypes)],
            ['region', 'in', 'range' => array_keys(Region::getList())],
            ['price_type', 'in', 'range' => array_keys(PriceType::getList())],
            ['timezone_name', 'in', 'range' => array_keys(Region::getTimezoneList())],
            ['status', 'in', 'range' => array_keys(ClientAccount::$statuses)],
            ['nal', 'in', 'range' => array_keys(ClientAccount::$nalTypes)],
            ['bill_rename1', 'in', 'range' => ['no', 'yes']],
            ['status', 'default', 'value' => ClientAccount::STATUS_INCOME],
            ['bik', BikValidator::className()],
            ['lk_balance_view_mode', 'in', 'range' => array_keys(ClientAccount::$balanceViewMode)],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(
            (new ClientAccount())->attributeLabels(),
            [
                'admin_email' => 'Email администратора'
            ]
        );
    }

    public function getModel()
    {
        return $this->clientM;
    }

    public function init()
    {
        if ($this->id) {

            $this->clientM = ClientAccount::findOne($this->id);
            if($this->clientM && $this->historyVersionRequestedDate) {
                $this->clientM->loadVersionOnDate($this->historyVersionRequestedDate);
            }
            if ($this->clientM === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->clientM->getAttributes(), false);
        } elseif ($this->contract_id) {
            $contract = ClientContract::findOne($this->contract_id);
            $contragent = ClientContragent::findOne($contract->contragent_id);
            if (!$this->super_id) {
                $this->super_id = $contract->super_id;
            }
            $this->clientM = new ClientAccount();
            $this->clientM->contract_id = $this->contract_id;
            $this->clientM->super_id = $this->super_id;
            $this->clientM->country_id = $contragent->country_id;
            $this->clientM->currency = Currency::defaultCurrencyByCountryId($contragent->country_id);
            $this->setAttributes($this->clientM->getAttributes(), false);
            $this->admin_contact_id = 0;
            $this->admin_is_active = 0;
            $this->voip_credit_limit_day = ClientAccount::DEFAULT_VOIP_CREDIT_LIMIT_DAY;
            $this->voip_is_day_calc = ClientAccount::DEFAULT_VOIP_IS_DAY_CALC;
            $this->bill_rename1 = 'no';
        } else {
            $this->clientM = new ClientAccount();
        }

        $this->mail_print = ($this->mail_print == 'yes') ? 1 : 0;
        $this->is_agent = ($this->is_agent == 'Y') ? 1 : 0;
    }

    public function save()
    {
        $client = $this->clientM;

        if ($this->getIsNewRecord())
            $this->is_active = 0;

        if ($this->credit < 0) {
            $this->credit = 0;
        }

        $this->is_agent = ($this->is_agent) ? 'Y' : 'N';
        $this->mail_print = ($this->mail_print) ? 'yes' : 'no';

        $client->setAttributes($this->getAttributes(null, ['historyVersionRequestedDate', 'id']), false);
        if($client && $this->historyVersionStoredDate) {
            $client->setHistoryVersionStoredDate($this->historyVersionStoredDate);
        }

        $contract = ClientContract::findOne($client->contract_id);
        $contragent = ClientContragent::findOne($contract->contragent_id);
        $client->country_id = $contragent->country_id;

        if (!$this->custom_properties) {
            if (
                !empty($this->bik) &&
                (empty($client->corr_acc) || empty($client->bank_name) || empty($client->bank_city))
            ) {
                $bik = Bik::findOne(['bik' => $this->bik]);

                if ($bik) {
                    $client->bik = $bik->bik;
                    $client->corr_acc = $bik->corr_acc;
                    $client->bank_name = $bik->bank_name;
                    $client->bank_city = $bik->bank_city;

                    $client->bank_properties =
                        'р/с ' . ($client->pay_acc ?: '') . "\n" .
                        $client->bank_name . ' ' . $client->bank_city .
                        ($client->corr_acc ? "\nк/с " . $client->corr_acc : '');
                }
            }
        }

        if ($client->save()) {
            if (!$client->client) {
                $client->client = 'id' . $client->id;
                $client->save();
            }
            if($this->admin_email){
                $contact = new ClientContact(["client_id" => $client->id]);
                $contact->addEmail($this->admin_email);
                $contact->setActiveAndOfficial();

                if ($contact->validate()) {
                    $contact->save();

                    $client->admin_contact_id = $contact->id;
                    $client->save();
                } else {
                    $this->addErrors($contact->getErrors());
                }

            }
            $this->setAttributes($client->getAttributes(), false);
            return true;
        } else
            $this->addErrors($client->getErrors());

        return false;
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }

    public function getCurrencyTypes()
    {
        return Currency::map();
    }

}
