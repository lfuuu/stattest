<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\OperationType;
use app\models\ClientAccount;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Универсальный счёт
 *
 * @link http://bugtracker.welltime.ru/jira/browse/BIL-1909
 *
 * @property int $id
 * @property int $operation_type_id
 * @property string $date Важен только месяц, день всегда 1
 * @property int $client_account_id
 * @property float $price
 * @property string $update_time
 * @property int $is_converted
 *
 * @property-read OperationType $operationType
 * @property-read ClientAccount $clientAccount
 * @property-read AccountEntry[] $accountEntries
 * @property-read \app\models\Bill $newBill
 *
 * @method static Bill findOne($condition)
 * @method static Bill[] findAll($condition)
 */
class Bill extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const CURRENT_STATEMENT = 'current_statement';

    protected $isAttributeTypecastBehavior = true;

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
            [['operation_type_id', 'client_account_id', 'is_converted'], 'integer'],
            [['price'], 'double'],
            [['date'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOperationType()
    {
        return $this->hasOne(OperationType::class, ['id' => 'operation_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'client_account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountEntries()
    {
        return $this->hasMany(AccountEntry::class, ['bill_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNewBill()
    {
        return $this->hasOne(\app\models\Bill::class, ['uu_bill_id' => 'id'])
            ->inverseOf('universalBill');
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
            ->andWhere(['<>', $accountEntryTableName . '.price_with_vat', 0])
            ->orderBy([
                $accountEntryTableName . '.date' => SORT_ASC,
                $accountEntryTableName . '.id' => SORT_ASC,
            ]);

        return $query;
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 7602373, 'message' => 'Счета'];
    }
}
