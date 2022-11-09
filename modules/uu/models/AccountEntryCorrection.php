<?php

namespace app\modules\uu\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;

/**
 * Корректировка проводок к счету
 *
 * @property int $client_account_id
 * @property string $bill_no
 * @property string $created_at
 * @property float $sum
 */
class AccountEntryCorrection extends ActiveRecord
{
    protected $isAttributeTypecastBehavior = true;

    public static function primaryKey()
    {
        return ['client_account_id', 'bill_no'];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_entry_correction';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'CreatedAt' => CreatedAt::class,
        ]);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_account_id'], 'integer'],
            ['bill_no', 'string'],
            [['sum'], 'double'],
        ];
    }
}
