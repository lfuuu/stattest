<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\models\ContractType;
use kartik\grid\GridView;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class TrunkContractTypeColumn extends DataColumn
{

    public $filterType = GridView::FILTER_SELECT2;
    public $filter = ['' => '----'];
    public $filterByBusinessProcessId = 0;

    private $contractTypes = [];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->contractTypes = ContractType::getList();

        if ((int)$this->filterByBusinessProcessId) {
            $this->filter += ContractType::getList($this->filterByBusinessProcessId);
        }
    }

    /**
     * Вернуть отображаемое значение ячейки
     *
     * @param ActiveRecord $model
     * @param string $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = $this->getDataCellValue($model, $key, $index);
        return isset($this->contractTypes[$value]) ? $this->contractTypes[$value] : '';
    }
}