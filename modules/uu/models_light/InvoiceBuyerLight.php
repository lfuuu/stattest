<?php

namespace app\modules\uu\models_light;

use app\models\ClientAccount;
use Yii;
use yii\base\Component;

class InvoiceBuyerLight extends Component implements InvoiceLightInterface
{

    public
        $name,
        $address,
        $tax_registration_id,
        $tax_registration_reason,
        $consignee,
        $currency,
        $currency_symbol;

    /**
     * @param ClientAccount $clientAccount
     */
    public function __construct(ClientAccount $clientAccount)
    {
        parent::__construct();

        $this->name = ($clientAccount->head_company ?: $clientAccount->company_full);
        $this->address = ($clientAccount->head_company_address_jur ?: $clientAccount->address_jur);
        $this->tax_registration_id = $clientAccount->contragent->inn;
        $this->tax_registration_reason = $clientAccount->contragent->kpp;
        $this->consignee = ($clientAccount->is_with_consignee && $clientAccount->consignee) ? $clientAccount->consignee : '------';
        $this->currency = $clientAccount->currencyModel->name;
        $this->currency_symbol = $clientAccount->currencyModel->symbol;
    }

    /**
     * @return string
     */
    public static function getKey()
    {
        return 'buyer';
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
            return 'Компания заказчик';
    }

    /**
     * @return array
     */
    public static function attributeLabels()
    {
        return [
            'name' => 'Наименование',
            'address' => 'Адрес',
            'tax_registration_id' => 'ИНН',
            'tax_registration_reason' => 'КПП',
            'consignee' => 'Грузополучатель',
            'currency' => 'Наименование валюты',
            'currency_symbol' => 'Символ валюты',
        ];
    }

}