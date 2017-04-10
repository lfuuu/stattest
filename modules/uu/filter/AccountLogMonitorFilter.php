<?php

namespace app\modules\uu\filter;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация AccountTariff
 */
class AccountLogMonitorFilter extends AccountTariff
{
    public $client_account_id = '';

    public $service_type_id = '';
    public $tariff_period_id = '';

    public $month = '';
    public $monthDateTime = null;

    public function rules()
    {
        return [
            [['client_account_id', 'tariff_period_id', 'service_type_id'], 'integer'],
            [['month'], 'string'],
        ];
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getMonthDateTime()
    {
        if ($this->month && !$this->monthDateTime) {
            $this->monthDateTime = new \DateTimeImmutable($this->month . '-01');
        }
        return $this->monthDateTime;
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = AccountTariff::find()
//            ->with('accountLogSetups')
//            ->with('accountLogPeriods')
//            ->with('accountLogResources')
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->client_account_id !== '' && $query->andWhere(['client_account_id' => $this->client_account_id]);
        $this->service_type_id !== '' && $query->andWhere(['service_type_id' => $this->service_type_id]);

        switch ($this->tariff_period_id) {
            case '':
                break;
            case TariffPeriod::IS_NOT_SET:
                $query->andWhere(['tariff_period_id' => null]);
                break;
            case TariffPeriod::IS_SET:
                $query->andWhere('tariff_period_id IS NOT NULL');
                break;
            default:
                $query->andWhere(['tariff_period_id' => $this->tariff_period_id]);
                break;
        }

        return $dataProvider;
    }
}
