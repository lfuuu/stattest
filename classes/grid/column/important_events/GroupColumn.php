<?php

namespace app\classes\grid\column\important_events;

use Yii;
use yii\db\ActiveRecord;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\classes\Html;
use app\classes\grid\column\ListTrait;
use app\models\important_events\ImportantEventsGroups;

class GroupColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $filterType = GridView::FILTER_SELECT2;
    public $group_id = null;

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->filter = ImportantEventsGroups::getList(true);
        parent::__construct($config);
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
        $recordId = $this->getDataCellValue($model, $key, $index);
        $recordTitle = $this->defaultRenderDataCellContent($model, $key, $index);
        return Html::a($recordTitle, ['/important_events/groups/edit', 'id' => $recordId]);
    }

}