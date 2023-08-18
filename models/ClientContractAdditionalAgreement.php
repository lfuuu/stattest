<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class ClientContractAdditionalAgreement
 *
 * @property int $id
 * @property int $contract_id
 * @property int $account_id
 * @property int $from_organization_id
 * @property int $to_organization_id
 * @property string $transfer_date
 */
class ClientContractAdditionalAgreement extends ActiveRecord
{

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract_additional_agreement';
    }

}
