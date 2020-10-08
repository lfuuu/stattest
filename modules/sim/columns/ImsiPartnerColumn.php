<?php

namespace app\modules\sim\columns;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp\models\NdcType;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\ImsiPartner;
use kartik\grid\GridView;
use Yii;


class ImsiPartnerColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;
    public $isWithNullAndNotNull = false;

    /**
     * StatusColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ImsiPartner::getList($this->isWithEmpty, $this->isWithNullAndNotNull);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' sim-imsi-partner-column';
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        $value = $this->getDataCellValue($model, $key, $index);
        if (is_null($value)) {
            return Yii::t('common', '(not set)');
        } else {
            return $this->filter[$value];
        }
    }
}