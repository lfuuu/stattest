<?php
namespace app\forms\client;

use app\models\ClientContract;
use app\models\Region;
use Yii;
use app\classes\Form;
use yii\base\Exception;
use app\models\Client;
use yii\helpers\ArrayHelper;

class ClientEditForm extends Form
{
    protected $clientM = null;

    public $id,
        $super_id,
        $contragent_id,
        $contract_id;

    public
        $client = '',
        $region = 99,
        $password = '',
        $password_type = 'plaintext',
        $status = 'income',
        $support = '',
        $login = '',
        $currency_bill = 'RUB',
        $telemarketing = '',
        $uid,
        $site_req_no = '',
        $hid_rtsaldo_date = '0000-00-00 00:00:00',
        $cli_1c,
        $con_1c,
        $country_id = 643,
        $credit_RUB = 0,
        $credit_USD = 0,
        $previous_reincarnation,
        $nds_calc_method = 1,
        $admin_contact_id = 0,
        $admin_is_active = 1,
        $is_bill_only_contract = 0,
        $is_bill_with_refund = 0,
        $is_active = 1,
        $is_closed = 0,
        $hid_rtsaldo_RUB = 0.00,
        $hid_rtsaldo_USD = 0.00,
        $balance_usd = 0.00,
        $sync_1c = 'no',
        $last_account_date,
        $comment = '',
        $usd_rate_percent = 0.0,
        $address_post = '',
        $address_post_real = '',
        $address_connect = '',
        $bik = '',
        $bank_properties = '',
        $currency = 'RUB',
        $stamp = 0,
        $nal = 'beznal',
        $credit = -1,
        $credit_size = 0,
        $sale_channel = 0,
        $user_impersonate = 'client',
        $phone_connect = '',
        $id_all4net = 0,
        $dealer_comment = '',
        $form_type = 'manual',
        $metro_id = 0,
        $payment_comment = '',
        $corr_acc = '',
        $pay_acc = '',
        $bank_name = '',
        $bank_city = '',
        $price_type = '',
        $voip_credit_limit = 0,
        $voip_disabled = 0,
        $voip_credit_limit_day = 0,
        $balance = 0.00,
        $voip_is_day_calc = 1,
        $mail_print = 'yes',
        $mail_who = '',
        $head_company = '',
        $head_company_address_jur = '',
        $bill_rename1 = 'no',
        $is_agent = 'N',
        $is_with_consignee = 0,
        $consignee = '',
        $is_upd_without_sign = 0,
        $is_blocked = 0,
        $timezone_name = 'Europe/Moscow';

    public function rules()
    {
        $rules = [
            [
                [
                    'client', 'password', 'password_type', 'comment', 'status', 'address_post', 'address_post_real', 'support', 'login', 'bik', 'bank_properties', 'currency', 'currency_bill',
                    'nal', 'telemarketing', 'uid', 'site_req_no', 'hid_rtsaldo_date', 'user_impersonate', 'address_connect', 'phone_connect',
                    'dealer_comment', 'form_type', 'payment_comment', 'cli_1c', 'con_1c', 'corr_acc', 'pay_acc', 'bank_name', 'bank_city', 'sync_1c', 'price_type',
                    'last_account_date', 'last_payed_voip_month', 'mail_who', 'head_company', 'head_company_address_jur', 'bill_rename1',
                    'consignee', 'timezone_name',
                ],
                'string'
            ],
            [
                [
                    'id', 'super_id', 'contragent_id', 'contract_id', 'country_id', 'stamp', 'sale_channel', 'credit_USD', 'credit_RUB', 'credit', 'id_all4net',
                    'metro_id', 'previous_reincarnation', 'voip_credit_limit', 'voip_disabled', 'voip_credit_limit_day', 'voip_is_day_calc', 'region', 'created',
                    'nds_calc_method', 'admin_contact_id', 'admin_is_active', 'is_bill_only_contract', 'is_bill_with_refund', 'is_with_consignee',
                    'is_upd_without_sign', 'is_active', 'is_blocked', 'is_closed', 'is_agent', 'mail_print'
                ],
                'integer'
            ],
            [
                [
                    'usd_rate_percent', 'hid_rtsaldo_RUB', 'hid_rtsaldo_USD', 'balance', 'balance_usd'
                ],
                'number'
            ],
            ['password_type', 'in', 'range' => ['plaintext', 'MD5']],
            ['status', 'in', 'range' => ['negotiations', 'testing', 'connecting', 'work', 'closed', 'tech_deny', 'telemarketing', 'income', 'deny', 'debt', 'double', 'trash', 'move', 'already', 'denial', 'once', 'reserved', 'suspended', 'operator', 'distr', 'blocked']],
            ['nal', 'in', 'range' => ['nal', 'beznal', 'prov']],
            ['sync_1c', 'in', 'range' => ['no', 'yes']],
            ['bill_rename1', 'in', 'range' => ['no', 'yes']],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new Client())->attributeLabels();
    }

    public function init()
    {
        if ($this->id) {
            $this->clientM = Client::findOne($this->id);
            if ($this->clientM === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->clientM->getAttributes(), false);
        } elseif ($this->contract_id) {
            $this->clientM = new Client();
            $this->clientM->contract_id = $this->contract_id;
            $this->contragent_id = $this->clientM->contragent_id = ClientContract::findOne($this->contract_id)->contragent_id;
            $this->super_id = $this->clientM->super_id = ClientContract::findOne($this->contract_id)->super_id;
        } else
            throw new Exception('You must send id or contract_id');

        $this->credit_size = ($this->credit < 1) ? 0 : $this->credit;
        $this->credit = ($this->credit < 1) ? 0 : 1;
        $this->mail_print = ($this->mail_print == 'yes') ? 1 : 0;
        $this->is_agent = ($this->is_agent == 'Y') ? 1 : 0;
    }

    public function save()
    {
        $client = $this->clientM;


        if ($this->credit < 1)
            $this->credit = -1;

        $this->is_agent = ($this->is_agent) ? 'Y' : 'N';
        $this->mail_print = ($this->mail_print) ? 'yes' : 'no';

        $client->setAttributes($this->getAttributes(), false);

        if ($client->save()) {
            if (!$client->client) {
                $client->client = 'id' . $client->id;
                $client->save();
            }
            $this->setAttributes($client->getAttributes(), false);
            return true;
        }
        return false;
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }

    public function getTimezones()
    {
        $arr = Region::find()->groupBy(['timezone_name'])->all();
        return ArrayHelper::map($arr, 'timezone_name', 'timezone_name');
    }

    public function getNalTypes()
    {
        return ['beznal' => 'Безнал', 'nal' => 'нал', 'prov' => 'пров'];
    }

    public function getCurrencyTypes()
    {
        return ['RUB' => 'RUB', 'USD' => 'USD'];
    }

    public function getFormTypes()
    {
        return ['manual' => 'ручное', 'bill' => 'при выставлении счета', 'payment' => 'при внесении платежа',];
    }

}