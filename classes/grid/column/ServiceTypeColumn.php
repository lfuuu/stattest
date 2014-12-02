<?php

namespace app\classes\grid\column;

use app\models\tariffication\ServiceType;
use kartik\grid\GridView;
use Yii;


class ServiceTypeColumn extends DataColumn
{
    public $attribute = 'service_type_id';
    public $value = 'serviceType.name';
    public $label = 'Тип услуги';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ServiceType::dao()->getList(true);
        parent::__construct($config);
    }
}