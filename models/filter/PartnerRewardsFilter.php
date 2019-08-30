<?php

namespace app\models\filter;

use app\classes\DynamicModel;
use app\models\Bill;
use app\models\BillLine;
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContractReward;
use app\models\ClientContragent;
use app\models\PartnerRewards;
use app\models\PartnerRewardsPermanent;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;

class PartnerRewardsFilter extends DynamicModel
{

    public
        $partner_contract_id,
        $payment_date_before,
        $payment_date_after,
        $isExtendsMode;

    public
        $contractsWithoutRewardSettings = [],
        $contractsWithIncorrectBusinessProcess = [],
        $summary = [],
        $possibleSummary = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['payment_date_before', 'payment_date_after'], 'string'],
            [['partner_contract_id',], 'integer'],
        ];
    }

    /**
     * @param bool $isExtendsMode
     */
    public function __construct($isExtendsMode = false)
    {
        parent::__construct();
        $this->isExtendsMode = $isExtendsMode;
    }

    /**
     * @return $this
     */
    public function load()
    {
        parent::load(Yii::$app->request->get(), 'filter');

        $this->contractsWithoutRewardSettings = $this->_getContractsWithoutRewardSettings();
        $this->contractsWithIncorrectBusinessProcess = $this->_getContractsWithIncorrectBusinessProcess();

        return $this;
    }

    /**
     * @return bool|ArrayDataProvider
     */
    public function search()
    {
        if ($this->partner_contract_id == '') {
            return false;
        }

        $query = new Query;
        $actual_from = <<<SQL
CASE
   WHEN line.service = 'usage_voip' THEN
     (
       SELECT MIN(usage_voip_inner.actual_from)
       FROM usage_voip usage_voip_inner
       WHERE usage_voip_inner.client = client.client AND usage_voip_inner.id = line.id_service
     )
   WHEN line.service = 'usage_virtpbx' THEN
     (
       SELECT MIN(usage_virtpbx_inner.actual_from)
       FROM usage_virtpbx usage_virtpbx_inner
       WHERE usage_virtpbx_inner.client = client.client AND usage_virtpbx_inner.id = line.id_service
     )
   WHEN line.service = 'usage_trunk' THEN
     (
       SELECT MIN(usage_trunk_inner.actual_from)
       FROM usage_trunk usage_trunk_inner
       WHERE usage_trunk_inner.id = line.id_service AND usage_trunk_inner.client_account_id = client.id
     )
   WHEN line.service = 'uu_account_tariff' THEN
     (
       SELECT convert(MIN(uu_account_tariff_log_inner.actual_from_utc), DATE)
       FROM uu_account_tariff uu_account_tariff_inner
        INNER JOIN uu_account_tariff_log uu_account_tariff_log_inner
          ON uu_account_tariff_log_inner.account_tariff_id = uu_account_tariff_inner.id
       WHERE uu_account_tariff_inner.id = line.id_service
     )
   END
SQL;
        $query->select([
            'rewards.*',
            'client_id' => 'client.id',
            'client_created' => 'client.created',
            'client.account_version',
            'contragent_name' => 'contragent.name',
            'bill_no' => 'bills.bill_no',
            'bill_paid' => 'bills.is_payed',
            'paid_summary' => 'bills.sum',
            'payment_date' => 'bills.payment_date',
            'usage_type' => 'line.service',
            'usage_id' => 'line.id_service',
            'usage_paid' => 'line.sum',
            'actual_from' => $actual_from,
        ]);

        // Определение источника генерации партнерского вознаграждения
        $partnerRewardsTableName = PartnerRewards::tableName();

        $query
            ->from(['rewards' => $partnerRewardsTableName])
            ->innerJoin(['bills' => Bill::tableName()], 'bills.id = rewards.bill_id')
            ->innerJoin(['client' => ClientAccount::tableName()], 'client.id = bills.client_id')
            ->innerJoin(['contract' => ClientContract::tableName()], 'contract.id = client.contract_id')
            ->innerJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
            ->innerJoin(['line' => BillLine::tableName()], 'line.pk = rewards.line_pk');

        $query
            ->andWhere(['contract.partner_contract_id' => $this->partner_contract_id])
            ->andWhere(['>=', 'line.sum', 0]);

        if (!$this->isExtendsMode) {
            $query->andWhere(['bills.is_payed' => Bill::STATUS_IS_PAID]);
        }

        if ($this->payment_date_before !== '') {
            $query->andWhere(['>=', new Expression('DATE_FORMAT(bills.payment_date, "%Y-%m")'), $this->payment_date_before]);
        }

        if ($this->payment_date_after !== '') {
            $query->andWhere(['<=', new Expression('DATE_FORMAT(bills.payment_date, "%Y-%m")'), $this->payment_date_after]);
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $this->_prepareData($query),
            'sort' => false,
            'pagination' => false,
        ]);

        return $dataProvider;
    }

    /**
     * @param Query $query
     * @return array
     */
    private function _prepareData(Query $query)
    {
        $data = [];

        $buffer = [];
        foreach ($query->each(1000) as $record) {
            if (!array_key_exists($record['client_id'], $data)) {
                $data[$record['client_id']] = [
                    'client_id' => $record['client_id'],
                    'contragent_name' => $record['contragent_name'],
                    'client_created' => $record['client_created'],
                ];
            }

            $fieldPrefix = '';
            $summaryField = 'summary';
            if ($this->isExtendsMode) {
                if ((int)$record['bill_paid'] !== Bill::STATUS_IS_PAID) {
                    $fieldPrefix = 'possible_';
                    $summaryField = 'possibleSummary';
                }
            }

            $data[$record['client_id']][$fieldPrefix . 'paid_summary_reward'] += $record['usage_paid'];
            $data[$record['client_id']]['details'][] = $record;

            $data[$record['client_id']][$fieldPrefix . 'once'] += $record['once'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_once'] += $record['percentage_once'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_of_fee'] += $record['percentage_of_fee'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_of_over'] += $record['percentage_of_over'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_of_margin'] += $record['percentage_of_margin'];

            // Расчет итоговых суммы для каждого клиента, который может иметь более одного счета.
            // Суммирование происходит по зараннее рассчитанному столбцу `sum` из таблицы `newbills`
            if (!isset($buffer['local'][$record['client_id']][$record['bill_id']])) {
                $buffer['local'][$record['client_id']][$record['bill_id']] = $record['bill_id'];
                $data[$record['client_id']][$fieldPrefix . 'paid_summary'] += $record['paid_summary'];
            }

            // Расчет итоговых суммы для всех клиентов. Игнорируется сам клиент, расчет происходит на основании
            // уникального поля `bill_id` таблицы `newbills`, суммируя столбец `sum`
            if (!isset($buffer['global'][$record['bill_id']])) {
                $buffer['global'][$record['bill_id']] = $record['bill_id'];
                $this->{$summaryField}['paid_summary'] += $record['paid_summary'];
            }

            // Суммируем все значение столбца `sum` таблицы `newbill_lines`
            $this->{$summaryField}['paid_summary_reward'] += $record['usage_paid'];

            $this->{$summaryField}['once'] += $record['once'];
            $this->{$summaryField}['percentage_once'] += $record['percentage_once'];
            $this->{$summaryField}['percentage_of_fee'] += $record['percentage_of_fee'];
            $this->{$summaryField}['percentage_of_over'] += $record['percentage_of_over'];
            $this->{$summaryField}['percentage_of_margin'] += $record['percentage_of_margin'];
        }
        unset($buffer);

        return $data;
    }

    /**
     * @return array
     */
    private function _getContractsWithoutRewardSettings()
    {
        $query = new Query;

        $query->select([
            'contract_id' => 'contract.id',
            'contragent_name' => 'contragent.name',
        ]);

        $query
            ->from([
                'partner' => new Expression('(
                    SELECT DISTINCT `partner_contract_id`
                    FROM `client_contract`
                    WHERE `partner_contract_id` > 0
                )'),
                'contragent' => ClientContragent::tableName(),
                'contract' => ClientContract::tableName(),
            ])
            ->leftJoin(['rewards' => ClientContractReward::tableName(),], 'rewards.contract_id = contract.id');

        $query
            ->where('partner.partner_contract_id = contract.id')
            ->andWhere('contract.contragent_id = contragent.id')
            ->andWhere(['IS', 'rewards.id', new Expression('NULL')]);

        $query->orderBy([
            'contragent.name' => SORT_ASC
        ]);

        return $query->all();
    }

    /**
     * @return array
     */
    private function _getContractsWithIncorrectBusinessProcess()
    {
        $query = new Query;

        $query->select([
            'contract_id' => 'contract.id',
            'contragent_name' => 'contragent.name',
        ]);

        $query
            ->from([
                'partner' => new Expression('(
                    SELECT DISTINCT `partner_contract_id`
                    FROM `client_contract`
                    WHERE `partner_contract_id` > 0
                )'),
                'contragent' => ClientContragent::tableName(),
                'contract' => ClientContract::tableName(),
            ])
            ->leftJoin(['rewards' => ClientContractReward::tableName(),], 'rewards.contract_id = contract.id');

        $query
            ->where('partner.partner_contract_id = contract.id')
            ->andWhere('contract.contragent_id = contragent.id')
            ->andWhere(['IS', 'rewards.id', new Expression('NULL')])
            ->andWhere(['!=', 'contract.business_id', Business::PARTNER]);

        $query->orderBy([
            'contragent.name' => SORT_ASC,
        ]);

        return $query->all();
    }

    /**
     * Функция расчета итоговых значений по вознаграждениям
     *
     * @param PartnerRewardsFilter $filterModel
     * @param string $type
     * @return string
     */
    public static function getTotalSummary($filterModel, $type = '')
    {
        $total = 0;
        $attribute = $type === '' ? 'summary' : 'possibleSummary';
        foreach ([
            'once', 'percentage_once', 'percentage_of_fee', 'percentage_of_over' , 'percentage_of_margin'
        ] as $key) {
            # Диалектика языка по подсчету итогового значения корректно работает на PHPv5
            # http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.indirect
            $total += $filterModel->{$attribute}[$key];
        }
        return number_format($total, 2, ',', ' ');
    }

    /**
     * Функция форматирования цены, требуемая в том числе и при экспорте отчета
     *
     * @param $price
     * @return string
     */
    public static function getNumberFormat($price)
    {
        return number_format($price, 2, ',', ' ');
    }
}