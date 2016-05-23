<?php

namespace app\classes\grid\column\billing;

use app\models\ContractType;
use yii\db\ActiveRecord;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use yii\db\Query;
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

        $query = ContractType::find();

        $this->contractTypes = ArrayHelper::map($query->all(), 'id', 'name');

        if ((int)$this->filterByBusinessProcessId) {
            $query->where(['business_process_id' => $this->filterByBusinessProcessId]);
            $this->filter += ArrayHelper::map($query->all(), 'id', 'name');
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
        return
            isset($this->contractTypes[$value])
                ? $this->contractTypes[$value]
                : '';
    }
}