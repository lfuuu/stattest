<?php

namespace app\classes\grid\column;

use app\models\voip\Destination;
use kartik\grid\GridView;

class DestinationVoipColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $attribute = 'destination_id';
    public $label = 'Направление';
    public $filterType = GridView::FILTER_SELECT2;

    /**
     * DestinationVoipColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->filter = Destination::getList($isWithEmpty = true);
        parent::__construct($config);
    }
}