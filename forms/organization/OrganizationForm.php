<?php
namespace app\forms\organization;

use Yii;
use app\classes\Form;
use app\models\Organization;


class OrganizationForm extends Form
{

    public
        $id,
        $firma,
        $actual_from,
        $country_id,
        $lang_code,
        $tax_system,
        $vat_rate = 0,
        $name,
        $full_name,
        $legal_address,
        $post_address,
        $registration_id,
        $tax_registration_id,
        $tax_registration_reason,
        $bank_account,
        $bank_name,
        $bank_correspondent_account,
        $bank_bik,
        $bank_swift,
        $contact_phone,
        $contact_fax,
        $contact_email,
        $contact_site,
        $logo_file_name,
        $stamp_file_name,
        $director_id = 0,
        $accountant_id = 0;

    const NEW_TITLE = 'Новая организация';
    const EDIT_TITLE = 'Обновление данных организации';

    // Система налогообложения
    public $tax_system_variants = [
        'ОСНО' => 'ОСНО',
        'УСН' => 'УСН',
    ];

    public function rules()
    {
        return [
            [['actual_from', 'name', 'firma'], 'required'],
            [['id', 'country_id', 'director_id', 'accountant_id', 'vat_rate',], 'integer'],
            [
                [
                    'lang_code', 'tax_system', 'full_name', 'legal_address', 'post_address',
                    'registration_id', 'tax_registration_id', 'tax_registration_reason', 'bank_account',
                    'bank_bik', 'bank_swift', 'contact_phone', 'contact_fax', 'contact_email',
                    'contact_site', 'logo_file_name', 'stamp_file_name',
                ],
                'string'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'actual_from' => 'Дата активации',
            'name' => 'Краткое название',
            'firma' => 'Код организации',
        ];
    }

    public function save($organization = false)
    {
        if (!($organization instanceof Organization))
            $organization = new Organization;
        $organization->setAttributes($this->getAttributes(), false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $organization->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $organization->id;

        return true;
    }

    public function duplicate(Organization $organization)
    {
        $record = clone $organization;
        unset($record->actual_from);
        $this->setAttributes($record->getAttributes());

        return $this;
    }

}