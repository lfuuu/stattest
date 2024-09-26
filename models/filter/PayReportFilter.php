<?php

namespace app\models\filter;

use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Payment;
use app\models\PaymentApiChannel;
use app\models\PaymentApiInfo;
use app\models\PaymentAtol;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class PayReportFilter
 */
class PayReportFilter extends Payment
{
    public $id = '';
    public $account_version = '';
    public $client_id = '';
    public $client_name = '';
    public $bill_no = '';
    public $bill_date_from = '';
    public $bill_date_to = '';
    public $currency = '';
    public $original_currency = '';
    public $payment_date_from = '';
    public $payment_date_to = '';
    public $oper_date_from = '';
    public $oper_date_to = '';
    public $add_date_from = '';
    public $add_date_to = '';
    public $organization_id = '';
    public $sum_from = '';
    public $sum_to = '';
    public $original_sum_from = '';
    public $original_sum_to = '';
    public $type = '';
    public $payment_no = '';
    public $comment = '';
    public $add_user = '';
    public $total = '';
    public $uuid = '';
    public $uuid_status = '';
    public $uuid_log = '';
    public $info_json = '';
    public $payment_api_log = '';
    public $is_with_links = '0';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'account_version',
                    'client_id',
                    'organization_id',
                    'user_id',
                    'add_user',
                    'sum_from',
                    'sum_to',
                    'original_sum_from',
                    'original_sum_to',
                    'add_user',
                    'uuid_status',
                    'is_with_links',
                ],
                'integer'
            ],
            [
                [
                    'bill_no',
                    'client_name',
                    'currency',
                    'original_currency',
                    'date_by',
                    'user_id',
                    'comment',
                    'bill_date_from',
                    'bill_date_to',
                    'payment_date_from',
                    'payment_date_to',
                    'oper_date_from',
                    'oper_date_to',
                    'add_date_from',
                    'add_date_to',
                    'type',
                    'payment_no',
                    'comment',
                    'uuid',
                    'uuid_log',
                    'payment_api_log',
                    'payment_type',
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
                'total' => 'Итого',
                'bill_date' => 'Дата счета',
                'uuid' => 'ID в онлайн-кассе',
                'uuid_status' => 'Статус отправки в онлайн-кассу',
                'uuid_log' => 'Лог отправки в онлайн-кассу',
                'payment_api_log' => 'Лог поиска ЛС',
                'info_json' => 'Данные платежа',
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
            ->joinWith('bill b')
            ->joinWith('paymentAtol')
            ->joinWith('apiInfo')
            ->with('apiInfo')
            ->with('apiChannel')
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $paymentAtolTableName = PaymentAtol::tableName();

        $this->id !== '' && $query->andWhere(['p.id' => $this->id]);

        if ($this->account_version !== '') {
            $query
                ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = p.client_id')
                ->andWhere(['c.account_version' => $this->account_version]);
        }

        $this->client_id !== '' && $query->andWhere(['p.client_id' => $this->client_id]);

        if ($this->organization_id !== '') {
            $query
                ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = p.client_id')
                ->innerJoin(['cc' => ClientContract::tableName()], 'cc.id = c.contract_id')
                ->andWhere(['cc.organization_id' => $this->organization_id]);
        }

        if ($this->client_name !== '') {
            $query
                ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = p.client_id')
                ->innerJoin(['cc' => ClientContract::tableName()], 'cc.id = c.contract_id')
                ->innerJoin(['cg' => ClientContragent::tableName()], 'cg.id = cc.contragent_id')
                ->andWhere(['like', 'cg.name', $this->client_name]);
        }

        $this->bill_no !== '' && $query->andWhere(['LIKE', 'p.bill_no', $this->bill_no]);
        $this->bill_date_from !== '' && $query->andWhere(['>=', 'b.bill_date', $this->bill_date_from]);
        $this->bill_date_to !== '' && $query->andWhere(['<=', 'b.bill_date', $this->bill_date_to]);
        $this->sum_from !== '' && $query->andWhere(['>=', 'p.sum', $this->sum_from]);
        $this->sum_to !== '' && $query->andWhere(['<=', 'p.sum', $this->sum_to]);
        $this->original_sum_from !== '' && $query->andWhere(['>=', 'p.original_sum', $this->original_sum_from]);
        $this->original_sum_to !== '' && $query->andWhere(['<=', 'p.original_sum', $this->original_sum_to]);

        $this->currency !== '' && $query->andWhere(['p.currency' => $this->currency]);
        $this->original_currency !== '' && $query->andWhere(['p.original_currency' => $this->original_currency]);

        $this->payment_type != '' && $query->andWhere(['p.payment_type' => $this->payment_type]);

        $this->payment_date_from !== '' && $query->andWhere(['>=', 'p.payment_date', $this->payment_date_from]);
        $this->payment_date_to !== '' && $query->andWhere(['<=', 'p.payment_date', $this->payment_date_to]);
        $this->oper_date_from !== '' && $query->andWhere(['>=', 'p.oper_date', $this->oper_date_from]);
        $this->oper_date_to !== '' && $query->andWhere(['<=', 'p.oper_date', $this->oper_date_to]);

        $this->add_date_from !== '' && $query->andWhere(['>=', 'p.add_date', $this->add_date_from]);
        $this->add_date_to !== '' && $query->andWhere(['<=', 'p.add_date', $this->add_date_to . ' 23:59:59']);
        $this->payment_no !== '' && $query->andWhere(['p.payment_no' => $this->payment_no]);
        $this->comment !== '' && $query->andWhere(['LIKE', 'p.comment', $this->comment]);
        $this->add_user !== '' && $query->andWhere(['p.add_user' => $this->add_user]);

        if ($this->type !== '') {
            $types = [];
            $typesEcashOperators = [];

            foreach ($this->type as $type_item) {
                list($type, $ecashOperator) = $this->_getTypeAndSubtype($type_item);
                if (!$ecashOperator) {
                    $types[] = $type;
                } else {
                    $typesEcashOperators[$type] ? $typesEcashOperators[$type][] = $ecashOperator : $typesEcashOperators[$type] = [$ecashOperator];
                }
            }

            if ($typesEcashOperators) {
                $condition = [];
                (count($typesEcashOperators) > 1 || $types) && $condition = ['or'];
                $types && $condition[] = ['type' => $types];

                foreach ($typesEcashOperators as $type => $ecashOperators) {
                    $condition ? $condition[] = ['and', ['type' => $type], ['ecash_operator' => $ecashOperators]] : $condition = ['type' => $type, 'ecash_operator' => $ecashOperators]; 
                }

                $query->andWhere($condition);
            }
            else {
                $query->andWhere(['type' => $types]);
            }
        }
        echo $query->createCommand()->getRawSql();
        exit;
        $this->uuid !== '' && $query->andWhere([$paymentAtolTableName . '.uuid' => $this->uuid]);
        $this->uuid_status !== '' && $query->andWhere([$paymentAtolTableName . '.uuid_status' => $this->uuid_status]);
        if($this->uuid_log !== '') {
            if (in_array(trim($this->uuid_log), ['не задано', '(не задано)'])) {
                $query->andWhere([$paymentAtolTableName . '.uuid_log' => null]);
            } else {
                $query->andWhere(['LIKE', $paymentAtolTableName . '.uuid_log', $this->uuid_log]);
            }
        }
        $this->payment_api_log !== '' && $query->andWhere(['LIKE', PaymentApiInfo::tableName() . '.log', $this->payment_api_log]);

        $this->total = "+" . $query->sum(new Expression('IF(p.sum > 0, p.sum, 0)')) . ' / ' . $query->sum(new Expression('IF(p.sum < 0, p.sum, 0)')) . ' ' . $this->currency;

        return $dataProvider;
    }

    /**
     * Список типов платежей
     *
     * @return array
     */
    public function getTypeList()
    {
        $pcList = PaymentApiChannel::getList();

        $keys = array_keys($pcList);
        array_walk($keys, function (&$k) {
            $k = Payment::TYPE_API . '_' . $k;
        });

        $values = array_values($pcList);
        array_walk($values, function (&$v) {
            $v = ucfirst(Payment::TYPE_API) .' ' . ucfirst($v);
        });

        $pcList = array_combine($keys, $values);

        return [
                self::TYPE_BANK => 'Банк',
                self::TYPE_PROV => 'Кассовый чек',
                self::TYPE_NEPROV => 'Наличка',
                self::TYPE_CREDITNOTE => 'Credit Note',
                self::TYPE_TERMINAL => 'Терминал',
                self::TYPE_ECASH . '_' . self::ECASH_YANDEX => 'ЭлЯндексДеньги',
                self::TYPE_ECASH . '_' . self::ECASH_SBERBANK => 'ЭлСбербанк',
                self::TYPE_ECASH . '_' . self::ECASH_PAYPAL => 'ЭлPayPall',
                self::TYPE_ECASH . '_' . self::ECASH_CYBERPLAT => 'ЭлCyberplat',
                self::TYPE_ECASH . '_' . self::ECASH_SBERBANK_ONLINE_MOBILE => 'ЭлSberOnlineMob',
                self::TYPE_ECASH . '_' . self::ECASH_QIWI => 'ЭлQiwi',
                self::TYPE_ECASH . '_' . self::ECASH_STRIPE => 'ЭлStripe',
            ] + $pcList;
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
        $pos = strpos($mixType, '_');
        if ($pos === false) {
            return [$mixType, false];
        }

        return [substr($mixType, 0, $pos), substr($mixType, $pos + 1)];
    }
}
