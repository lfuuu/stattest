<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\models\UsageTrunk;
use kartik\grid\GridView;
use yii\db\ActiveRecord;

class TrunkSuperСlientColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;
    public $filter = ['' => ' ---- '];
    protected $trunkIdToSuperClientName = [];

    public function __construct($config = [])
    {
        // одному trunk_id может соответствовать несколько суперклиентов
        // чтобы не плодить дублей в списке, приходится делать следующее:
        // хранить trunkIdToSuperClientName и отображать это в ячейке
        $this->trunkIdToSuperClientName = UsageTrunk::getSuperClientList(true);

        $filter = [];
        foreach ($this->trunkIdToSuperClientName as $trunkId => $name) {
            // а фильтр строить по другому массиву, у которого в качестве ключа - список id
            // чтобы получить такой массив - строим инвертированный и потом его инвертируем
            if (isset($filter[$name])) {
                $filter[$name] .= ',' . $trunkId;
            } else {
                $filter[$name] = $trunkId;
            }
        }
        $this->filter += array_flip($filter);

        parent::__construct($config);
        $this->filterInputOptions['class'] .= ' trunk-superclient-column';
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
        $value = $this->getDataCellValue($model, $key, $index); // trunk_id, а не trunk_ids!
        return isset($this->trunkIdToSuperClientName[$value]) ? (string)$this->trunkIdToSuperClientName[$value] : $value;
    }
}