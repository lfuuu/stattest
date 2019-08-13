<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\Organization;
use kartik\grid\GridView;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;

class OrganizationColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $isAddLink = true;

    public $filterType = GridView::FILTER_SELECT2;

    /**
     * @param array $config
     * @throws \yii\base\Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Organization::dao()->getList(true);
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
        $value = $this->getDataCellValue($model, $key, $index);
        $strValue = $this->defaultRenderDataCellContent($model, $key, $index);
        if (is_null($value)) {
            return Yii::t('common', '(not set)');
        } elseif ($this->isAddLink) {
            return Html::a($strValue, Url::toRoute(['/organization/edit/', 'id' => $value]));
        } else {
            return $strValue;
        }
    }
}