<?php
/**
 * Список истории изменений
 *
 * @var app\classes\BaseView $this
 * @var HistoryChangesFilter $filterModel
 */

use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\HistoryActionColumn;
use app\classes\grid\column\universal\HistoryModelColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\UserColumn;
use app\classes\grid\GridView;
use app\models\filter\HistoryChangesFilter;
use app\models\HistoryChanges;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title = 'История изменения', 'url' => '/history/'],
    ],
]) ?>

<?php
$models = [];
$dataFunction = function (HistoryChanges $historyChanges, $fieldName) use (&$models) {
    if (!$historyChanges->$fieldName) {
        return '';
    }

    $modelName = $historyChanges->model;
    if (!isset($models[$modelName]) && class_exists($modelName)) {
        $models[$modelName] = new $modelName();
    }
    $model = $models[$modelName];

    $lines = [];

    $data = json_decode($historyChanges->$fieldName, true);
    foreach ($data as $field => $value) {
        if ($model) {
            if (method_exists($model, 'prepareHistoryValue')) {
                $value = $model::prepareHistoryValue($field, $value);
            }
            $field = $model->getAttributeLabel($field);
        }
        $lines[] = '<p>' . $field . ': ' . $value . '</p>';
    }

    return implode(PHP_EOL, $lines);
};

$baseView = $this;
$columns = [
    [
        'attribute' => 'action',
        'class' => HistoryActionColumn::class,
    ],
    [
        'attribute' => 'model',
        'class' => HistoryModelColumn::class,
    ],
    [
        'attribute' => 'model_id',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'parent_model_id',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'user_id',
        'class' => UserColumn::class,
        'indexBy' => 'id',
    ],
    [
        'attribute' => 'created_at',
        'class' => DateTimeRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'prev_data_json',
        'format' => 'raw',
        'value' => function (HistoryChanges $historyChanges) use (&$dataFunction) {
            return $dataFunction($historyChanges, 'prev_data_json');
        },
    ],
    [
        'attribute' => 'data_json',
        'format' => 'raw',
        'value' => function (HistoryChanges $historyChanges) use (&$dataFunction) {
            return $dataFunction($historyChanges, 'data_json');
        },
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);