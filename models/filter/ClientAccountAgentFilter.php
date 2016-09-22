<?php

namespace app\models\filter;

use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\data\ArrayDataProvider;
use app\classes\DynamicModel;
use app\classes\stats\AgentReport;
use app\classes\DateTimeWithUserTimezone;

class ClientAccountAgentFilter extends DynamicModel
{

    public
        $partner_contract_id,
        $date;

    public
        $contractsWithoutReward,
        $contractsWithIncorrectBP,
        $summary = [];

    /** @var AgentReport $report */
    private
        $report,
        $filterDateFrom,
        $filterDateTo;

    public function rules()
    {
        return [
            [['date',], 'string'],
            [['partner_contract_id',], 'integer'],
        ];
    }

    public function load()
    {
        $this->report = new AgentReport;

        parent::load(Yii::$app->request->get(), 'filter');

        $this->contractsWithoutReward = $this->report->getWithoutRewardContracts();
        $this->contractsWithIncorrectBP = $this->report->getContractsWithIncorrectBP();

        list($dateFrom, $dateTo) = explode(' - ', $this->date);

        $this->filterDateFrom =
            !empty($dateFrom)
                ? $dateFrom
                : (new DateTimeWithUserTimezone('first day of previous month'))->format(DateTimeZoneHelper::DATE_FORMAT);
        $this->filterDateTo =
            !empty($dateTo)
                ? $dateTo :
                (new DateTimeWithUserTimezone('last day of previous month'))->format(DateTimeZoneHelper::DATE_FORMAT);

        return $this;
    }

    /**
     * Фильтровать
     *
     * @return ArrayDataProvider
     */
    public function search()
    {
        $data = [];

        if ($this->partner_contract_id) {
            $data = $this->report->run($this->partner_contract_id, $this->filterDateFrom, $this->filterDateTo);
            $this->summary = $this->report->summary;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'sort' => false,
            'pagination' => false,
        ]);

        return $dataProvider;
    }
}