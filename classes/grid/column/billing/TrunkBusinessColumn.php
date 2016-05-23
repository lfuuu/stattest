<?php

namespace app\classes\grid\column\billing;

use app\models\Business;
use app\models\BusinessProcess;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;

class TrunkBusinessColumn extends DataColumn
{

    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $filter = ['' => '----'];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $query =
            BusinessProcess::find()
                ->where(['business_id' => Business::OPERATOR]);

        $this->filter += ArrayHelper::map($query->all(), 'id', 'name');
    }

}