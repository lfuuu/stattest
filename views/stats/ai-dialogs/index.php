<?php
/**
 * Вознаграждения партнеров
 *
 * @var \app\classes\BaseView $this
 * @var \app\models\filter\AiDialogFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\DropdownColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\billing\api\ApiMethod;
use app\models\billing\api\ApiRaw;
use app\models\filter\BillingApiFilter;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

echo Html::formLabel($this->title = 'ИИ-агент: Диалоги');

echo Breadcrumbs::widget([
    'links' => [
        'Статистика',
        ['label' => $this->title, 'url' => $baseUrl = Url::toRoute('stats/ai-dialogs')]
    ],
]);

if (!$filterModel->accountId) {
    echo Html::tag('div', 'Клиент не выбран', ['class' => 'alert alert-danger']);
    return;
}

if (!$filterModel->isLoad) {
    ?>

    <div class="row">
        <div class="col-sm-6 text-left">
            <?php $total = $filterModel->getTotal(); ?>
            <div class="well">Итоговое потребление: <?=$total['sum_sec']?> секунд / <?=$total['sum_min']?> минут</div>
        </div>
    </div>

    <?php
}
$form = ActiveForm::begin(['method' => 'get', 'action' => $baseUrl]);

$columns = [
    [
        'attribute' => 'action_start',
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'agent_id',
        'class' => \app\classes\grid\column\universal\IntegerColumn::class,
    ],
    [
        'attribute' => 'agent_name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'duration',
        'class' => IntegerRangeColumn::class,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);

ActiveForm::end(); ?>