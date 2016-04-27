<?php

namespace app\classes\uu\model;

use app\models\ClientAccount;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Счет
 *
 * @property int $id
 * @property string $date важен только месяц. День всегда 1.
 * @property int $client_account_id
 * @property float $price
 * @property string $update_time
 *
 * @property ClientAccount $clientAccount
 * @property AccountEntry[] $accountEntries
 */
class Bill extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    public static function tableName()
    {
        return 'uu_bill';
    }

    public function rules()
    {
        return [
            [['client_account_id'], 'integer'],
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
        return $this->hasMany(AccountEntry::className(), ['bill_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/bill', 'BillFilter[id]' => $this->id]);
    }
}
