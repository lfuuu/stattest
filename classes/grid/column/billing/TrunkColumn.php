<?php

namespace app\classes\grid\column\billing;

use kartik\grid\GridView;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\classes\Html;
use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\billing\Trunk;

class TrunkColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $filterByIds = [];
    public $filterByServerId = '';

    public function __construct($config = [])
    {
        parent::__construct($config);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' trunk-column';

        $this->filter = Trunk::getList($this->filterByServerId, true);

        // если выбран оператор, то в списке показать только его транки
        if ($this->filterByIds) {
            $filterByIds = $this->filterByIds;
            $newFilter = [];
            foreach ($this->filter as $trunkId => $trunk) {
                if ($trunkId === '' || isset($filterByIds[$trunkId])) {
                    $newFilter[$trunkId] = $trunk;
                }
            }
            $this->filter = $newFilter;

            // это извращение из-за того, что php 5.5 не понимает ARRAY_FILTER_USE_KEY
            // когда везде будет php 5.6, то верхний кусок выпилить, а нижний раскомментировать
            /*
            $filterByIds = $this->filterByIds;
            $this->filter = array_filter(
                $this->filter,
                function ($trunkId) use ($filterByIds) {
                    return $trunkId === '' || isset($filterByIds[$trunkId]);
                });
            */
        }
    }

}