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
 * @link http://bugtracker.welltime.ru/jira/browse/BIL-1909
 * Счет на postpaid никогда не создается
 * При подключении новой услуги prepaid сразу же создается счет на эту услугу. Если в течение календарных суток подключается вторая услуга, то она добавляется в первый счет.
 *      Если в новые календарные сутки - создается новый счет. В этот счет идет подключение подключение и абонентка. Ресурсы и минималка никогда сюда не попадают.
 * 1го числа каждого месяца создается новый счет за все prepaid абонентки, не вошедшие в отдельные счета (то есть абонентки автопродлеваемых услуг), все ресурсы и минималки.
 *      Подключение в этот счет не должно попасть.
 * Из любого счета всегда исключаются строки с нулевой стоимостью. Если в счете нет ни одной строки - он автоматически удаляется.
 *
 * Иными словами можно сказать:
 * проводки за подключение группируются посуточно и на их основе создаются счета. В эти же счета добавляются проводки за абонентку от этих же услуг за эту же дату
 * все остальные проводки (is_default) группируются помесячно и на их основе создаются счета.
 *
 * @property int $id
 * @property string $date У обычного счета (is_default) важен только месяц, день всегда 1. У счета на доплату (когда создается новая услуга) - день фактический
 * @property int $client_account_id
 * @property float $price
 * @property string $update_time
 * @property int $is_default
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
            [['client_account_id', 'is_default'], 'integer'],
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
    public function getCurrency()
    {
        return $this->clientAccount->currency;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/bill', 'BillFilter[id]' => $this->id]);
    }

}
