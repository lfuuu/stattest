<?php
namespace app\models;

use Yii;
use app\dao\BillDao;
use yii\db\ActiveRecord;
use app\queries\BillQuery;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $bill_no        номер счета для отображения
 * @property string $bill_date      дата счета
 * @property int $client_id      id лицевого счета
 * @property string $currency       валюта. значения: USD, RUB
 * @property int $is_approved    Признак проведенности счета. 1 - проведен, влияет на балланс. 0 - не проведен, не влияет на баланс.
 * @property string $sum            итоговая сумма, влияющая на баланс. не включает задаток. не включает не проведенные строки
 * @property string $sum_with_unapproved  итоговая сумма. не включает задаток. включает не проведенный строки
 * @property int $is_payed       признак оплаченности счета 0 - не оплачен, 1 - оплачен, 2 - оплачен частично, 3 - ??
 * @property string $comment
 * @property int $inv2to1        ??
 * @property float $inv_rub        ??
 * @property string $postreg        ?? date
 * @property int $courier_id     ??
 * @property string $nal            ??  значения: beznal,nal,prov
 * @property string $sync_1c        ??  значения: yes, no
 * @property string $push_1c        ??  значения: yes, no
 * @property string $state_1c       ??
 * @property string $is_rollback    1 - счет на возврат. 0 - обычный
 * @property string $payed_ya       ??
 * @property string $editor         ??  значения: stat, admin
 * @property int $is_lk_show     ??
 * @property string $doc_date       ??
 * @property int $is_user_prepay ??
 * @property string $bill_no_ext        ??
 * @property string $bill_no_ext_date   ??
 * @property int $price_include_vat   ??
 * @property int $biller_version
 * @property int $uu_bill_id
 * @property ClientAccount $clientAccount   ??
 * @property BillLine[] $lines   ??
 * @property Transaction[] $transactions   ??
 */
class Bill extends ActiveRecord
{
    const MINIMUM_BILL_DATE = '2000-01-01';

    const STATUS_NOT_PAID = 0;
    const STATUS_IS_PAID = 1;
    const STATUS_PAID_IN_PART = 2;

    public static $paidStatuses = [
        self::STATUS_NOT_PAID => 'Не оплачен',
        self::STATUS_IS_PAID => 'Оплачен',
        self::STATUS_PAID_IN_PART => 'Оплачен частично',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'newbills';
    }

    /**
     * @return array
     */
    public function transactions()
    {
        return [
            'default' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            \app\classes\behaviors\HistoryChanges::className(),
            \app\classes\behaviors\PartnerRewards::className(),
        ];
    }

    /**
     * @return BillQuery
     */
    public static function find()
    {
        return new BillQuery(get_called_class());
    }

    /**
     * @return BillDao
     */
    public static function dao()
    {
        return BillDao::me();
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'sum' => 'Сумма',
            'sum_with_unapproved' => 'Сумма (не проведенная)',
            'postreg' => 'Почтовый реестр',
            'courier_id' => 'Курьер',
            'state_1c' => 'Статус заказа',
            'doc_date' => 'Дата документа',
            'bill_no_ext_date' => 'Дата внешнего счета',
            'bill_no_ext' => 'Внешний номер',
            'nal' => 'Предпологаемый тип платежа',
        ];
    }

    /**
     * @param string $field
     * @param string $value
     * @return string
     */
    public function prepareHistoryValue($field, $value)
    {
        switch ($field) {
            case 'courier_id':
                if ($courier = Courier::findOne($value)) {
                    /** @var Courier $courier */
                    return $value . ' (' . $courier->name . ')';
                }
                break;
        }

        return $value;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLines()
    {
        return $this->hasMany(BillLine::className(), ['bill_no' => 'bill_no']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::className(), ['bill_id' => 'id']);
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(['id' => $this->client_id]);

        if (!$account) {
            return null;
        }

        $account->loadVersionOnDate($this->bill_date);

        return $account;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return Bill::dao()->isClosed($this);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExtendsInfo()
    {
        return $this->hasOne(BillExtendsInfo::className(), ['bill_no' => 'bill_no']);
    }

    /**
     * @param int $clientId
     * @return boolean|\app\models\Bill
     */
    public static function getLastUnpaidBill($clientId)
    {
        $fromDate = self::MINIMUM_BILL_DATE;

        if (($clientAccount = ClientAccount::findOne($clientId)) === null) {
            return false;
        }

        if ($lastSaldo = Saldo::getLastSaldo($clientAccount->id)) {
            $fromDate = $lastSaldo->ts;
        }

        // First unpaid bill
        $bill = self::find()
                ->where([
                    'client_id' => $clientAccount->id,
                    'currency' => $clientAccount->currency,
                    'biller_version' => ClientAccount::VERSION_BILLER_USAGE
                ])
                ->andWhere(['in', 'is_payed', [self::STATUS_NOT_PAID, self::STATUS_PAID_IN_PART]])
                ->andWhere(['>', 'bill_date', $fromDate])
                ->orderBy('bill_date')
                ->one();

        if ($bill === null) {
            // Last bill
            $bill = self::find()
                    ->where([
                        'client_id' => $clientAccount->id,
                        'currency' => $clientAccount->currency,
                        'biller_version' => ClientAccount::VERSION_BILLER_USAGE
                    ])
                    ->andWhere(['is_payed' => 1])
                    ->andWhere(['>', 'bill_date', $fromDate])
                    ->orderBy(['bill_date' => SORT_DESC])
                    ->one();
        }

        return $bill !== null ? $bill : false;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::toRoute(['/', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $this->bill_no]);
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeDelete()
    {
        Trouble::deleteAll(['bill_no' => $this->bill_no]);

        foreach ($this->lines as $line) {
            Transaction::dao()->markDeletedByBillLine($line);
        }

        Yii::$app->db->createCommand(
            'update log_newbills set bill_no = :billNoWithDate where bill_no = :billNo',
            [':billNoWithDate' => $this->bill_no . date('dHs'), ':billNo' => $this->bill_no]
        )->execute();

        return parent::beforeDelete();
    }

}
