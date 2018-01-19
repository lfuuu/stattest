<?php

namespace app\models;

use app\classes\behaviors\BillChangeLog;
use app\classes\behaviors\CheckBillPaymentOverdue;
use app\classes\behaviors\PartnerRewardsCalculation;
use app\classes\behaviors\SetBillPaymentOverdue;
use app\classes\model\ActiveRecord;
use app\classes\Utils;
use app\dao\BillDao;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\Bill as uuBill;
use app\queries\BillQuery;
use Yii;
use yii\helpers\Url;

/**
 * Class Bill
 *
 * @property int $id
 * @property string $bill_no        номер счета для отображения
 * @property string $bill_date      дата счета
 * @property int $client_id      id лицевого счета
 * @property string $currency       валюта. значения: USD, RUB
 * @property int $is_approved    Признак проведенности счета. 1 - проведен, влияет на балланс. 0 - не проведен, не влияет на баланс.
 * @property float $sum            итоговая сумма, влияющая на баланс. не включает задаток. не включает не проведенные строки
 * @property float $sum_with_unapproved  итоговая сумма. не включает задаток. включает не проведенный строки
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
 * @property int $is_show_in_lk     ??
 * @property string $doc_date       ??
 * @property int $is_user_prepay ??
 * @property string $bill_no_ext        ??
 * @property string $bill_no_ext_date   ??
 * @property int $price_include_vat   ??
 * @property int $biller_version
 * @property int $uu_bill_id
 * @property int $organization_id
 * @property string $pay_bill_until
 * @property int $is_pay_overdue
 *
 * @property-read ClientAccount $clientAccount   ??
 * @property-read BillLine[] $lines   ??
 * @property-read Transaction[] $transactions   ??
 * @property-read Currency $currencyModel
 * @property-read Payment $creditNote
 * @property-read uuBill $universalBill
 */
class Bill extends ActiveRecord
{
    const MINIMUM_BILL_DATE = '2000-01-01';

    const PAY_NOT_PAYED = 0;
    const PAY_FULL_PAYED = 1;
    const PAY_PART_PAYED = 2;

    const STATUS_NOT_PAID = 0;
    const STATUS_IS_PAID = 1;
    const STATUS_PAID_IN_PART = 2;

    const DOC_TYPE_INCOMEGOOD = 'incomegood';
    const DOC_TYPE_BILL = 'bill';
    const DOC_TYPE_UNKNOWN = 'unknown';

    const TYPE_1C = '1c';
    const TYPE_STAT = 'stat';

    const TRIGGER_CHECK_OVERDUE = 'trigger_check_overdue';

    const PERCENT_PAYMENT_PAY = 95; // сколько процентов оплаты считать, что счет оплачен полностью

    public static $paidStatuses = [
        self::STATUS_NOT_PAID => 'Не оплачен',
        self::STATUS_IS_PAID => 'Оплачен',
        self::STATUS_PAID_IN_PART => 'Оплачен частично',
    ];


    public $creatorId = null;
    public $logMessage = null;

    public $isHistoryVersioning = false;

    public $isSetPayOverdue = null;

    /**
     * Название таблицы
     *
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
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'PartnerRewardsCalculate' => PartnerRewardsCalculation::className(),
            'SetBillPaymentOverdue' => SetBillPaymentOverdue::className(),
            'CheckBillPaymentOverdue' => CheckBillPaymentOverdue::className(),
            'BillChangeLog' => BillChangeLog::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];
    }

    /**
     * Query модели
     *
     * @return BillQuery
     */
    public static function find()
    {
        return new BillQuery(get_called_class());
    }

    /**
     * Dao
     *
     * @return BillDao
     */
    public static function dao()
    {
        return BillDao::me();
    }

    /**
     * Навзщение полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'client_id' => 'ЛС',
            'bill_no' => 'Номер счета',
            'bill_date' => 'Дата счета',
            'sum' => 'Сумма',
            'currency' => 'Валюта',
            'sum_with_unapproved' => 'Сумма (не проведенная)',
            'postreg' => 'Почтовый реестр',
            'courier_id' => 'Курьер',
            'state_1c' => 'Статус заказа',
            'doc_date' => 'Дата документа',
            'bill_no_ext_date' => 'Дата внешнего счета',
            'bill_no_ext' => 'Внешний номер',
            'comment' => 'Комментарий',
            'nal' => 'Предпологаемый тип платежа',
            'is_pay' => 'Счет оплачен',
            'pay_bill_until' => 'Оплатить счет до',
            'is_pay_overdue' => 'Просрочена оплата счета'
        ];
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {
            case 'courier_id':
                if ($courier = Courier::findOne(['id' => $value])) {
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
     * Закрыт ли счет?
     *
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
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyModel()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUniversalBill()
    {
        return $this->hasOne(uuBill::className(), ['id' => 'uu_bill_id']);
    }

    /**
     * @return Payment
     */
    public function getCreditNote()
    {
        return self::dao()->getCreditNote($this);

    }

    /**
     * Получаем последний неоплаченный счет
     *
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
        return Url::toRoute([
            '/',
            'module' => 'newaccounts',
            'action' => 'bill_view',
            'bill' => $this->bill_no
        ]);
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

    /**
     * @param bool $isInsert
     * @return bool
     */
    public function beforeSave($isInsert)
    {
        // проставляем организацию счета, если не установлена
        if ($this->bill_date && $this->client_id && !$this->organization_id) {
            $account = ClientAccount::findOne(['id' => $this->client_id])->loadVersionOnDate($this->bill_date);
            $this->organization_id = $account->contract->organization_id;
        }

        return parent::beforeSave($isInsert);
    }

    /**
     * Добавление строчки в счет
     *
     * @param string $item
     * @param float $amount
     * @param float $price
     * @param string $type
     * @param \DateTime|\DateTimeImmutable|string $dateFrom
     * @param \DateTime|\DateTimeImmutable|string $dateTo
     * @return BillLine
     */
    public function addLine($item, $amount, $price, $type = BillLine::LINE_TYPE_SERVICE, $dateFrom = null, $dateTo = null)
    {
        if (!$dateFrom) {
            $dateFrom = Utils::dateBeginOfPreviousMonth($this->bill_date);
        } elseif ($dateFrom instanceof \DateTimeImmutable || $dateFrom instanceof \DateTime) {
            $dateFrom =  $dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        }

        if (!$dateTo) {
            $dateTo = Utils::dateBeginOfPreviousMonth($this->bill_date);
        } elseif ($dateTo instanceof \DateTimeImmutable || $dateTo instanceof \DateTime) {
            $dateTo =  $dateTo->format(DateTimeZoneHelper::DATE_FORMAT);
        }

        $line = new BillLine();
        $line->bill_no = $this->bill_no;
        $line->sort = ((int)BillLine::find()
                ->where(['bill_no' => $this->bill_no])
                ->max('sort')) + 1;
        $line->item = $item;
        $line->amount = $amount;
        $line->type = $type;
        $line->date_from = $dateFrom;
        $line->date_to = $dateTo;
        $line->tax_rate = $this->clientAccount->getTaxRate();
        $line->price = $price;
        // $line->service = $service;
        // $line->id_service = $id_service;
        $line->calculateSum($this->price_include_vat);
        $line->save();

        return $line;
    }
}
