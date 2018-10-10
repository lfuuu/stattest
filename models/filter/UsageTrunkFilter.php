<?php

namespace app\models\filter;

use app\models\billing\Trunk;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\UsageTrunk;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class UsageTrunkFilter extends UsageTrunk
{

    public
        $connection_point_id,
        $trunk_ids,
        $contragent_id,
        $contract_number,
        $contract_type_id,
        $business_process_id,
        $trunk_id,
        $what_is_enabled,
        $description,
        $actual_from_from,
        $actual_from_to,
        $comment,
        $federal_district,
        $group_term_trunk,
        $group_orig_trunk,
        $number_a_orig,
        $number_b_orig,
        $number_a_term,
        $number_b_term;

    private $trunkIDs = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'connection_point_id',
                    'contragent_id',
                    'contract_type_id',
                    'business_process_id',
                    'trunk_id',
                ],
                'integer'
            ],
            [['what_is_enabled', 'trunk_ids', 'contract_number', 'description', 'comment', 'federal_district'], 'string'],
            [['actual_from_from', 'actual_from_to'], 'string'],
        ];
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'connection_point_id' => 'Точка подключения',
            'trunk_ids' => 'Супер-клиент',
            'contragent_id' => 'Контрагент',
            'contract_number' => '№ договора',
            'contract_type_id' => 'Тип договора',
            'business_process_id' => 'Бизнес-процесс',
            'trunk_id' => 'Транк',
            'description' => 'Описание',
            'actual_from' => 'Дата подключения',
            'comment' => 'Комментарии',
            'federal_district' => 'Федеральный округ',
            'group_orig_trunk' => 'Группа оригинации',
            'group_term_trunk' => 'Группа терминации',
            'number_a_orig' => 'Номер А (ориг.)',
            'number_b_orig' => 'Номер В (ориг.)',
            'number_a_term' => 'Номер А (терм.)',
            'number_b_term' => 'Номер В (терм.)',
        ];
    }

    /**
     * @param array $data
     * @param null|string $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        $params = parent::load($data, $formName);

        if (isset($this->trunk_ids) && $this->trunk_ids !== '') {
            $this->trunkIDs = explode(',', $this->trunk_ids);
        }

        return $params;
    }

    /**
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = new Query;

        $query->select([
            'usage_trunk_id' => 'trunk.id',
            'trunk.*',
            'contract_number' => 'contract.number',
            'business_process_id' => 'contract.business_process_id',
            'contract_type_id' => 'contract.contract_type_id',
            'federal_district' => 'contract.federal_district',
        ]);

        $query
            ->from(['trunk' => UsageTrunk::tableName()])
            ->leftJoin(
                ['client' => ClientAccount::tableName()],
                'client.id = trunk.client_account_id'
            )
            ->leftJoin(
                ['contract' => ClientContract::tableName()],
                'contract.id = client.contract_id'
            );

        $query->andWhere([
            '<=',
            'trunk.activation_dt',
            new Expression('CAST(NOW() AS DATETIME)')
        ]);
        $query->andWhere([
            '>=',
            'trunk.expire_dt',
            new Expression('CAST(NOW() AS DATETIME)')
        ]);

        /**
         * Установка условий фильтрации
         */
        !empty($this->connection_point_id) && $query->andWhere(['trunk.connection_point_id' => $this->connection_point_id]);
        is_array($this->trunkIDs) && count($this->trunkIDs) > 0 && $query->andWhere([
            'IN',
            'trunk.trunk_id',
            $this->trunkIDs
        ]);
        !empty($this->contragent_id) && $query->andWhere(['contract.contragent_id' => $this->contragent_id]);
        !empty($this->contract_number) && $query->andWhere(['LIKE', 'contract.number', $this->contract_number]);
        !empty($this->contract_type_id) && $query->andWhere(['contract.contract_type_id' => $this->contract_type_id]);
        !empty($this->business_process_id) && $query->andWhere(['contract.business_process_id' => $this->business_process_id]);
        !empty($this->trunk_id) && $query->andWhere(['trunk.trunk_id' => $this->trunk_id]);
        !empty($this->description) && $query->andWhere(['LIKE', 'trunk.description', $this->description]);
        !empty($this->comment) && $query->andWhere(['LIKE', 'trunk.comment', $this->comment]);
        !empty($this->federal_district) && $query->andWhere(['LIKE', 'contract.federal_district', $this->federal_district]);

        !empty($this->actual_from_from) && $query->andWhere(['>=', 'trunk.actual_from', $this->actual_from_from]);
        !empty($this->actual_from_to) && $query->andWhere(['<=', 'trunk.actual_from', $this->actual_from_to]);

        switch ($this->what_is_enabled) {
            case Trunk::TRUNK_DIRECTION_ORIG:
                {
                    $query->andWhere(['trunk.orig_enabled' => 1]);
                    break;
                }
            case Trunk::TRUNK_DIRECTION_TERM:
                {
                    $query->andWhere(['trunk.term_enabled' => 1]);
                    break;
                }
            case Trunk::TRUNK_DIRECTION_BOTH:
                {
                    $query->andWhere([
                        'trunk.orig_enabled' => 1,
                        'trunk.term_enabled' => 1,
                    ]);
                    break;
                }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => [
                'attributes' => array_keys($this->attributeLabels()),
                'defaultOrder' => [
                    'trunk_id' => SORT_DESC,
                ],
            ],
        ]);

        return $dataProvider;
    }
}