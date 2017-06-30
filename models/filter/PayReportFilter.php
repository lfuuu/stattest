<?php

namespace app\models\filter;

use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Currency;
use app\models\Payment;
use app\models\PaymentAtol;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class PayReportFilter
 */
class PayReportFilter extends Payment
{
    const SORT_PAYMENT_DATE = 'payment_date';
    const SORT_ADD_DATE = 'add_date';
    const SORT_OPER_DATE = 'oper_date';

    public $id = '';
    public $client_id = '';
    public $client_name = '';
    public $bill_no = '';
    public $currency = '';
    public $payment_date_from = '';
    public $payment_date_to = '';
    public $oper_date_from = '';
    public $oper_date_to = '';
    public $add_date_from = '';
    public $add_date_to = '';
    public $organization_id = '';
    public $sum_from = '';
    public $sum_to = '';
    public $type = '';
    public $payment_no = '';
    public $comment = '';
    public $add_user = '';
    public $sort_field = self::SORT_ADD_DATE;
    public $sort_direction = SORT_ASC;
    public $total = '';
    public $uuid = '';
    public $uuid_status = '';
    public $uuid_log = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'client_id',
                    'organization_id',
                    'user_id',
                    'add_user',
                    'sum_from',
                    'sum_to',
                    'add_user',
                    'sort_direction',
                    'uuid_status',
                ],
                'integer'
            ],
            [
                [
                    'bill_no',
                    'currency',
                    'date_by',
                    'user_id',
                    'comment',
                    'payment_date_from',
                    'payment_date_to',
                    'oper_date_from',
                    'oper_date_to',
                    'add_date_from',
                    'add_date_to',
                    'type',
                    'payment_no',
                    'comment',
                    'sort_field',
                    'uuid',
                    'uuid_log',
                ],
                'string'
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'organization_id' => 'Организация',
                'client_name' => 'Клиент',
                'sort_field' => 'Сортировать по:',
                'sort_direction' => 'Направление сортировки',
                'total' => 'Итого'
            ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Payment::find()
            ->alias('p')
            ->orderBy([$this->sort_field => (int)$this->sort_direction]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['p.id' => $this->id]);
        $this->client_id !== '' && $query->andWhere(['p.client_id' => $this->client_id]);

        if ($this->organization_id !== '') {
            $query
                ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = p.client_id')
                ->innerJoin(['cc' => ClientContract::tableName()], 'cc.id = c.contract_id')
                ->andWhere(['cc.organization_id' => $this->organization_id]);
        }

        $this->bill_no !== '' && $query->andWhere(['LIKE', 'p.bill_no', $this->bill_no]);
        $this->sum_from !== '' && $query->andWhere(['>=', 'p.sum', $this->sum_from]);
        $this->sum_to !== '' && $query->andWhere(['<=', 'p.sum', $this->sum_to]);

        $this->currency === '' && $this->currency = Currency::RUB;
        $query->andWhere(['p.currency' => $this->currency]);

        $this->payment_date_from !== '' && $query->andWhere(['>=', 'p.payment_date', $this->payment_date_from]);
        $this->payment_date_to !== '' && $query->andWhere(['<=', 'p.payment_date', $this->payment_date_to]);
        $this->oper_date_from !== '' && $query->andWhere(['>=', 'p.oper_date', $this->oper_date_from]);
        $this->oper_date_to !== '' && $query->andWhere(['<=', 'p.oper_date', $this->oper_date_to]);

        $this->add_date_from !== '' && $query->andWhere(['>=', 'p.add_date', $this->add_date_from]);
        $this->add_date_to !== '' && $query->andWhere(['<=', 'p.add_date', $this->add_date_to]);
        $this->payment_no !== '' && $query->andWhere(['p.payment_no' => $this->payment_no]);
        $this->comment !== '' && $query->andWhere(['LIKE', 'p.comment', $this->comment]);
        $this->add_user !== '' && $query->andWhere(['p.add_user' => $this->add_user]);

        if ($this->type !== '') {
            list($type, $ecashOperator) = $this->_getTypeAndSubtype($this->type);
            $query->andWhere(['type' => $type]);
            $ecashOperator && $query->andWhere(['ecash_operator' => $ecashOperator]);
        }

        $this->uuid !== '' && $query->andWhere(['p.uuid' => $this->uuid]);
        $this->uuid_status !== '' && $query->andWhere(['p.uuid_status' => $this->uuid_status]);
        $this->uuid_log !== '' && $query->andWhere(['LIKE', 'p.uuid_log', $this->uuid_log]);

        $this->total = "+" . $query->sum(new Expression('IF(sum > 0, sum, 0)')) . ' / ' . $query->sum(new Expression('IF(sum < 0, sum, 0)')) . ' ' . $this->currency;

        return $dataProvider;
    }

    /**
     * Список типов платежей
     *
     * @return array
     */
    public function getTypeList()
    {
        return [
            '' => '----',
            self::TYPE_BANK => 'Банк',
            self::TYPE_PROV => 'Кассовый чек',
            self::TYPE_NEPROV => 'Наличка',
            self::TYPE_ECASH . '_' . self::ECASH_YANDEX => 'ЭлЯндексДеньги',
            self::TYPE_ECASH . '_' . self::ECASH_SBERBANK => 'ЭлСбербанк',
            self::TYPE_ECASH . '_' . self::ECASH_PAYPAL => 'ЭлPayPall',
            self::TYPE_ECASH . '_' . self::ECASH_CYBERPLAT => 'ЭлCyberplat'
        ];
    }

    /**
     * Список статусов
     *
     * @return array
     */
    public function getUuidStatusList()
    {
        return ['' => '----'] + PaymentAtol::$uuidStatus;
    }

    /**
     * Разбор типа
     *
     * @param string $mixType
     * @return array
     */
    private function _getTypeAndSubtype($mixType)
    {
        return explode('_', $mixType . '_');
    }

    /**
     * Список дат для сортировки
     *
     * @return array
     */
    public function getSortDateList()
    {
        return [
            self::SORT_ADD_DATE => $this->getAttributeLabel(self::SORT_ADD_DATE),
            self::SORT_OPER_DATE => $this->getAttributeLabel(self::SORT_OPER_DATE),
            self::SORT_PAYMENT_DATE => $this->getAttributeLabel(self::SORT_PAYMENT_DATE)
        ];
    }

    /**
     * Направление сортировки
     *
     * @return array
     */
    public function getSortDirection()
    {
        return [
            SORT_ASC => 'По возрастанию',
            SORT_DESC => 'По убыванию',
        ];
    }
}
