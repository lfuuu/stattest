<?php

namespace app\classes\grid\column\billing;

use kartik\grid\GridView;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\classes\Html;
use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\billing\Trunk;

/**
 * Частный случай вывода информации о транке (услуга транк + транк)
 */
class UsageTrunkColumn extends TrunkColumn
{

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
        return
            isset($this->filter[$value])
                ?
                '(' . $model['id'] . ') ' .
                Html::a(
                    (string)$this->filter[$value],
                    Url::toRoute(['usage/trunk/edit', 'id' => $model['id']]),
                    ['target' => '_blank']
                )
                : $value;
    }

}