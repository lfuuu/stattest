<?php

namespace app\models\light_models\uu;

use Yii;
use yii\base\Component;
use app\models\Organization;

class InvoiceSellerLight extends Component implements InvoiceLightInterface
{

    public
        $name,
        $legal_address,
        $post_address,
        $country,
        $tax_registration_id,
        $tax_registration_reason,
        $contact_email,
        $contact_phone,
        $contact_fax,
        $director,
        $accountant,
        $bank;

    /**
     * @param string $language
     * @param Organization $organization
     * @param $invoiceSetting
     */
    public function __construct($language, Organization $organization, $invoiceSetting)
    {
        parent::__construct();

        $this->name = $organization->name;
        $this->legal_address = $organization->legal_address;
        $this->post_address = $organization->post_address;
        $this->country = $organization->country->name;
        $this->tax_registration_id = $organization->tax_registration_id;
        $this->tax_registration_reason = $organization->tax_registration_reason;
        $this->contact_email = $organization->contact_email;
        $this->contact_phone = $organization->contact_phone;
        $this->contact_fax = $organization->contact_fax;
        $this->director = (array)(new InvoicePersonLight($organization->director->setLanguage($language)));
        $this->accountant = (array)(new InvoicePersonLight($organization->accountant->setLanguage($language)));
        $this->bank = (array)(new InvoiceBankLight(
            !is_null($invoiceSetting)
                ? $organization->getSettlementAccount($invoiceSetting->settlement_account_type_id)
                : $organization->settlementAccount
            )
        );
    }


    /**
     * @return string
     */
    public static function getKey()
    {
        return 'seller';
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return 'Компания поставщик';
    }

    /**
     * @return []
     */
    public static function attributeLabels()
    {
        return [
            'name' => 'Наименование',
            'legal_address' => 'Юридический адрес',
            'post_address' => 'Почтовый адрес',
            'country' => 'Название страны',
            'tax_registration_id' => 'ИНН',
            'tax_registration_reason' => 'КПП',
            'contact_email' => 'Контактный e-mail',
            'contact_phone' => 'Контактный номер телефона',
            'contact_fax' => 'Контактный номер факса',
            'director' => InvoicePersonLight::attributeLabels(),
            'accountant' => InvoicePersonLight::attributeLabels(),
            'bank' => InvoiceBankLight::attributeLabels(),
        ];
    }

}