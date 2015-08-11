<?php

namespace app\classes\grid\column;

use kartik\grid\GridView;
use app\models\voip\Destination;

class DestinationVoipColumn extends DataColumn
{

    public $attribute = 'destination_id';
    public $label = 'Направление';
    public $value = 'destination.name';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => ' -------- '] + Destination::dao()->getList(false);
        parent::__construct($config);
    }

}