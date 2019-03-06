<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;

/**
 * @property integer account_tariff_id
 * @property integer trouble_id
 */
class AccountTrouble extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_troubles';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['account_tariff_id', 'trouble_id',], 'integer'],
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->trouble_id;
    }
}
