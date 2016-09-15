<?php

namespace app\models\light_models\uu;

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
        $consignee;

    /**
     * @param ClientAccount $clientAccount
     */
    public function __construct(ClientAccount $clientAccount)
    {
        parent::__construct();

        $this->name = ($clientAccount->head_company ?: $clientAccount->company_full);
        $this->address = ($clientAccount->head_company_address_jur ?: $clientAccount->address_jur);
        $this->tax_registration_id = $clientAccount->inn;
        $this->tax_registration_reason = $clientAccount->kpp;
        $this->consignee = ($clientAccount->is_with_consignee && $clientAccount->consignee) ? $clientAccount->consignee : '------';
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
     * @return []
     */
    public static function attributeLabels()
    {
        return [
            'name' => 'Наименование',
            'address' => 'Адрес',
            'tax_registration_id' => 'ИНН',
            'tax_registration_reason' => 'КПП',
            'consignee' => 'Грузополучатель',
        ];
    }

}