<?php

namespace app\classes\grid\column\billing;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\classes\Html;
use app\models\billing\Trunk;

class TrunkContragentColumn extends DataColumn
{

    public $filterType = GridView::FILTER_SELECT2;
    public $filter = ['' => '----'];
    public $trunkId = '';
    public $connectionPointId = '';

    private $contragents = [];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $result = Trunk::dao()->getContragents($this->trunkId, $this->connectionPointId);

        $this->filter += ArrayHelper::map($result, 'id', 'name');
        $this->contragents = ArrayHelper::map($result, 'id', 'name', 'client_account_id');

        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' trunk-contragent-column';
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
        $value = $model['client_account_id'];

        if (isset($this->contragents[$value]) && is_array($this->contragents[$value])) {
            reset($this->contragents[$value]);
            list($contragent_id, $contragent_name) = each($this->contragents[$value]);

            return
                '(' .
                Html::a($value, Url::toRoute(['client/view', 'id' => $value]) . '#contragent' . $contragent_id,
                    ['target' => '_blank']) .
                ') ' .
                $contragent_name;
        }

        return $value;
    }
}