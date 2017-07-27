<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Счет
 *
 * @link http://bugtracker.welltime.ru/jira/browse/BIL-1909
 *
 * @property int $id
 * @property string $date Важен только месяц, день всегда 1
 * @property int $client_account_id
 * @property float $price
 * @property string $update_time
 * @property int $is_converted
 *
 * @property ClientAccount $clientAccount
 * @property AccountEntry[] $accountEntries
 */
class Bill extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const CURRENT_STATEMENT = 'current_statement';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_bill';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_account_id', 'is_converted'], 'integer'],
            [['price'], 'double'],
            [['date'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountEntries()
    {
        return $this->hasMany(AccountEntry::className(), ['bill_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->clientAccount->currency;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/uu/bill', 'BillFilter[id]' => $this->id]);
    }

    /**
     * Получение несконвертированных проводок
     *
     * @param int $accountId
     * @return ActiveQuery
     */
    public static function getUnconvertedAccountEntries($accountId)
    {
        $billTableName = self::tableName();
        $accountEntryTableName = AccountEntry::tableName();
        $query = AccountEntry::find()
            ->joinWith('bill')
            ->where([
                $billTableName . '.client_account_id' => $accountId,
                $billTableName . '.is_converted' => 0,
            ])
            ->andWhere(['>', $accountEntryTableName . '.price_with_vat', 0]);

        return $query;
    }
}
