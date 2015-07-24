<?php
namespace app\forms\client;

use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Currency;
use app\models\HistoryVersion;
use app\models\PriceType;
use app\models\Region;
use Yii;
use app\classes\Form;
use yii\base\Exception;

class AccountEditForm extends Form
{
    protected $clientM = null;
    public $deferredDate = null;

    public $id,
        $super_id,
        $contract_id;

    public
        $client,
        $region = Region::MOSCOW,
        $status,
        $address_post,
        $address_post_real,
        $address_connect,
        $currency,
        $stamp,
        $nal,
        $credit,
        $credit_size,
        $sale_channel,
        $phone_connect,
        $form_type,
        $price_type,
        $voip_credit_limit,
        $voip_disabled,
        $voip_credit_limit_day = 1000,
        $voip_is_day_calc,
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
        $is_active;

    public function rules()
    {
        $rules = [
            [
                [
                    'client', 'address_post', 'address_post_real', 'address_connect', 'phone_connect',
                    'mail_who', 'head_company', 'head_company_address_jur', 'consignee',
                ],
                'string'
            ],
            [
                [
                    'client', 'address_post', 'address_post_real', 'address_connect', 'phone_connect',
                    'mail_who', 'head_company', 'head_company_address_jur', 'consignee',
                ],
                'default', 'value' => ''
            ],
            [
                [
                    'id', 'super_id', 'contract_id', 'stamp', 'sale_channel', 'credit', 'credit_size', 'voip_credit_limit',
                    'voip_disabled', 'voip_credit_limit_day', 'voip_is_day_calc', 'is_with_consignee', 'is_upd_without_sign',
                    'is_agent', 'mail_print'
                ],
                'integer'
            ],
            [
                [
                    'stamp', 'sale_channel', 'credit', 'voip_credit_limit', 'is_agent', 'mail_print',
                    'voip_disabled', 'voip_credit_limit_day', 'voip_is_day_calc', 'is_with_consignee', 'is_upd_without_sign',
                ],
                'default', 'value' => 0
            ],
            ['currency', 'in', 'range' => array_keys(Currency::map())],
            ['form_type', 'in', 'range' => array_keys(ClientAccount::$formTypes)],
            ['region', 'in', 'range' => array_keys(Region::getList())],
            ['price_type', 'in', 'range' => array_keys(PriceType::getList())],
            ['timezone_name', 'in', 'range' => array_keys(Region::getTimezoneList())],
            ['status', 'in', 'range' => array_keys(ClientAccount::$statuses)],
            ['nal', 'in', 'range' => array_keys(ClientAccount::$nalTypes)],
            ['bill_rename1', 'in', 'range' => ['no', 'yes']],

            ['voip_credit_limit_day', 'default', 'value' => 1000],
            ['status', 'default', 'value' => ClientAccount::STATUS_INCOME],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientAccount())->attributeLabels();
    }

    public function getModel()
    {
        return $this->clientM;
    }

    public function init()
    {
        if ($this->id) {
            $this->clientM = ClientAccount::findOne($this->id)->loadVersionOnDate($this->deferredDate);
            if ($this->clientM === null) {
                throw new Exception('Contract not found');
            }
            $this->setAttributes($this->clientM->getAttributes(), false);
        } elseif ($this->contract_id) {
            $this->clientM = new ClientAccount();
            $this->clientM->contract_id = $this->contract_id;
            $this->super_id = $this->clientM->super_id = !$this->super_id ? ClientContract::findOne($this->contract_id)->super_id : $this->super_id;
        } else {
            $this->clientM = new ClientAccount();
        }

        if($this->credit == -1){
            $this->credit = 0;
            $this->credit_size = 0;
        } elseif($this->credit == 0) {
            $this->credit = 1;
            $this->credit_size = 0;
        }else{
            $this->credit_size = $this->credit;
            $this->credit = 1;
        }

        $this->sale_channel = (!is_numeric($this->sale_channel)) ? 0 : $this->sale_channel;
        $this->mail_print = ($this->mail_print == 'yes') ? 1 : 0;
        $this->is_agent = ($this->is_agent == 'Y') ? 1 : 0;
    }

    public function save()
    {
        $client = $this->clientM;

        if ($this->getIsNewRecord())
            $this->is_active = 0;

        if ($this->credit && $this->credit_size > 0) {
            $this->credit = $this->credit_size;
        } elseif ($this->credit) {
            $this->credit = 0;
        } else{
            $this->credit = -1;
        }

        $this->is_agent = ($this->is_agent) ? 'Y' : 'N';
        $this->mail_print = ($this->mail_print) ? 'yes' : 'no';

        $client->setAttributes($this->getAttributes(null, ['deferredDate', 'id']), false);

        if ($client->save()) {
            if (!$client->client) {
                $client->client = 'id' . $client->id;
                $client->save();
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