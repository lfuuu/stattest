<?php
namespace app\forms\organization;

use Yii;
use app\classes\Form;
use app\models\Organization;

class OrganizationForm extends Form
{

    public
        $id,
        $organization_id = 0,
        $firma,
        $actual_from,
        $country_id,
        $lang_code,
        $is_simple_tax_system = 0,
        $vat_rate = 0,
        $registration_id,
        $tax_registration_id,
        $tax_registration_reason,
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

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['actual_from', 'firma'], 'required'],
            [
                [
                    'id',
                    'country_id',
                    'director_id',
                    'accountant_id',
                    'vat_rate',
                    'director_id',
                    'accountant_id',
                    'organization_id',
                ],
                'integer'
            ],
            [
                [
                    'lang_code',
                    'is_simple_tax_system',
                    'registration_id',
                    'tax_registration_id',
                    'tax_registration_reason',
                    'contact_phone',
                    'contact_fax',
                    'contact_email',
                    'contact_site',
                    'logo_file_name',
                    'stamp_file_name',
                    'organization_id'
                ],
                'string'
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'actual_from' => 'Дата активации',
            'firma' => 'Код организации',
        ];
    }

    /**
     * @param Organization|false $organization
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function save($organization = false)
    {
        if (!($organization instanceof Organization)) {
            $organization = new Organization;
        }
        $organization->setAttributes($this->getAttributes(), false);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $organization->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->organization_id = $organization->organization_id;

        return true;
    }

    /**
     * @param Organization $organization
     */
    public function duplicate(Organization $organization)
    {
        $record = clone $organization;
        unset($record->actual_from);
        unset($record->id);
        $this->setAttributes($record->getAttributes());
    }

}
