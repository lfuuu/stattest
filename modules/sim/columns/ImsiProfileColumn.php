<?php


namespace app\modules\sim\columns;

use app\classes\grid\column\DataColumn;
use app\modules\sim\models\ImsiProfile;
use kartik\grid\GridView;

class ImsiProfileColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;
    public $isWithNullAndNotNull = false;

    /**
     * ProfileColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ImsiProfile::getList($this->isWithEmpty, $this->isWithNullAndNotNull);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' sim-imsi-profile-column';
    }

}