<?php

namespace app\classes\grid\column\billing;

use yii\db\ActiveRecord;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\billing\Trunk;

class TrunkTypeColumn extends DataColumn
{

    public $filterType = GridView::FILTER_SELECT2;
    public $filter = ['' => '----',];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter += Trunk::$trunkTypes;
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
        $result = [];

        if (is_array($model)) {
            $result[] = ($model['orig_enabled'] == 1 ? GridView::ICON_ACTIVE : GridView::ICON_INACTIVE);
            $result[] = ($model['term_enabled'] == 1 ? GridView::ICON_ACTIVE : GridView::ICON_INACTIVE);
        }

        return implode(' / ', $result);
    }
}