<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;

/**
 * Расширяющая таблица, присоединяемая к таблице uu_account_tariff
 *
 * @property int $account_tariff_id
 * @property string $test_connect_date
 * @property string $disconnect_date
 * @property string $date_sale
 * @property string $date_before_sale
 */
class AccountTariffHeap extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff_heap';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['account_tariff_id', 'integer'],
            [['test_connect_date', 'disconnect_date', 'date_sale', 'date_before_sale'], 'string'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_tariff_id']);
    }
}