<?php
/**
 * Вывести блок "Загрузить/сохранить фильтры"
 *
 * @var app\classes\BaseView $this
 * @var \app\classes\model\ActiveRecord $filterModel
 * @var array $columns
 */

use app\classes\Html;
use yii\jui\AutoComplete;
use yii\web\JsExpression;

if (!method_exists($filterModel, 'getFilterQueriesForAutocomplete')) {
    return;
}

$divId = 'filterQueryDiv';
$filterQueries = $filterModel->getFilterQueriesForAutocomplete();
echo $this->render('//layouts/_toggleButton',
    [
        'divSelector' => '#' . $divId,
        'title' => sprintf('Фильтр (%d)', count($filterQueries)),
        // всплывашка с z-index=0, поэтому приходится так извращаться
        'onclick' => "
             var \$pushpinTableHeader = \$('#pushpinTableHeader');
             if (\$pushpinTableHeader.hasClass('active')) {
                \$pushpinTableHeader.trigger('click');
             }
        ",
    ]);


?>

<div id="<?= $divId ?>" class="collapse">
    <div class="well">

        <dl class="dl-horizontal">
            <?php
            // текущие значения фильтра
            $filterValues = $filterModel->getObjectNotEmptyValues();
            foreach ($filterValues as $filterValueKey => $filterValue) :
                $filterBeautyValue = $filterModel->getBeautyValue($filterValueKey, $filterValue, $columns);
                ?>
                <dt><?= $filterModel->getFilterQueryAttributeLabel($filterValueKey) ?></dt>
                <dd><?= is_array($filterBeautyValue) ? implode(', ', $filterBeautyValue) : $filterBeautyValue ?></dd>
            <?php endforeach; ?>
        </dl>

        <?php
        // имя фильтра
        if ($filterQueries) {

            // есть сохраненные значения - выпадашка
            $filterQueryId = Yii::$app->request->get('filterQueryId');
            if ($filterQueryId && isset($filterQueries[$filterQueryId])) {
                $filterQueryName = $filterQueries[$filterQueryId]['label'];
            } else {
                $filterQueryId = $filterQueryName = '';
            }

            echo AutoComplete::widget([
                'id' => 'filterQueryName',
                'name' => 'filterQueryName',
                'value' => $filterQueryName,
                'clientOptions' => [
                    'minLength' => 0,
                    'delay' => 0,
                    'source' => array_values($filterQueries), // индексы должны быть автоинкрементные
                    'select' => new JsExpression("function(event, ui) { \$('#filterQueryId').val(ui.item.id).trigger('showHideButtons'); }"),
                ],
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'Новый или ранее сохраненный фильтр',
                    'onFocus' => "\$(this).autocomplete('search', \$(this).val());",
                ],
            ]);

        } else {
            // текстовое поле
            echo Html::input(
                'text',
                'filterQueryName',
                '',
                [
                    'id' => 'filterQueryName',
                    'class' => 'form-control',
                    'placeholder' => 'Новый фильтр',
                ]
            );
        }

        // id фильтра
        echo Html::hiddenInput(
            'filterQueryId',
            Yii::$app->request->get('filterQueryId'),
            [
                'id' => 'filterQueryId',
            ]
        );

        echo $this->render('//layouts/_button', [
            'text' => 'Создать новый',
            'glyphicon' => 'glyphicon-save',
            'params' => [
                'id' => 'filterQueryButtonAdd',
                'class' => 'btn btn-primary collapse',
            ],
        ]);


        echo $this->render('//layouts/_button', [
            'text' => 'Загрузить',
            'glyphicon' => 'glyphicon-open',
            'params' => [
                'id' => 'filterQueryButtonLoad',
                'class' => 'btn btn-primary collapse',
            ],
        ]);

        echo $this->render('//layouts/_button', [
            'text' => 'Заменить',
            'glyphicon' => 'glyphicon-repeat',
            'params' => [
                'id' => 'filterQueryButtonReplace',
                'class' => 'btn btn-warning collapse',
            ],
        ]);

        echo $this->render('//layouts/_button', [
            'text' => 'Удалить',
            'glyphicon' => 'glyphicon-trash',
            'params' => [
                'id' => 'filterQueryButtonDelete',
                'class' => 'btn btn-danger collapse',
            ],
        ]);

        ?>
    </div>
</div>
