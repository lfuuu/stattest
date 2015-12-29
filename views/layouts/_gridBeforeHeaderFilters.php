<?php
/**
 * Фильтры грида
 * Вынесено в отдельный файл, чтобы не захлямлять колонки, ибо фильтровать надо, а показывать нет
 * Используются оригинальные *Column, чтобы не изобретать велосипед
 *
 * @var \yii\web\View $this
 * @var ActiveRecord $filterModel
 * @var [] $filterColumns
 */

use app\classes\Html;
use yii\base\Widget;
use yii\db\ActiveRecord;

?>
<div class="beforeHeaderFilters">

    <div class="row">

        <?php foreach ($filterColumns as $i => $filterColumn) : ?>
        <?php
        $filterOptions = isset($filterColumn['filterOptions']) ? $filterColumn['filterOptions'] : [];
        !isset($filterOptions['class']) && $filterOptions['class'] = '';
        strpos($filterOptions['class'], 'col-sm-') === false && $filterOptions['class'] .= ' col-sm-3'; // если класс ширины не указан, указать его
        ?>
        <div <?= Html::renderTagAttributes($filterOptions) ?>>
            <label><?= isset($filterColumn['label']) ? $filterColumn['label'] : $filterModel->getAttributeLabel($filterColumn['attribute']) ?></label>
            <div>
                <?php
                $grid = new stdClass();
                $grid->filterModel = $filterModel;
                $grid->bootstrap = true;
                $grid->showPageSummary = true;
                $filterColumn['grid'] = $grid; // для совместимости

                /** @var app\classes\grid\column\DataColumn $column */
                $column = Yii::createObject($filterColumn);

                if (is_string($column->filter)) {
                    echo $column->filter;
                } else {
                    /** @var Widget $widgetName */
                    $widgetName = $column->filterType;
                    echo $widgetName::widget([
                        'model' => $filterModel,
                        'attribute' => $filterColumn['attribute'],
                        'data' => $column->filter,
                    ]);
                }
                ?>
            </div>
        </div>

        <?php if (!(($i + 1) % 4)) : ?>
    </div>
    <div class="row">
        <?php endif ?>

        <?php endforeach ?>

    </div>
