<?php

namespace app\modules\mtt\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\mtt_raw\MttRaw;
use kartik\grid\GridView;

class SrcNumberColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;
    public $accountId = null;

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $list = MttRaw::getEmptyList($this->isWithEmpty);
        $this->filter = $this->accountId ?
            $list + MttRaw::getVoipListByClientAccountId($this->accountId) : $list;

        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' mtt-src-number-column';
    }
}