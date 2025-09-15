<?php
/**
 * Статистика / ИИ-агент: Диалоги
 *
 * @var \app\classes\BaseView $this
 * @var \app\models\filter\AiDialogFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\AiDialogRaw;
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
        'label' => 'Время начала', 
        'value' => function(AiDialogRaw $raw) {
            $t = (new DateTimeImmutable($raw->action_start, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->setTimezone($raw->clientAccount->timezone);
            
            return $t->format(DateTimeZoneHelper::DATETIME_FORMAT);
        },
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'agent_id',
        'label' => 'ID Агента',
        'class' => \app\classes\grid\column\universal\IntegerColumn::class,
    ],
    [
        'attribute' => 'agent_name',
        'label' => 'Имя агента',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'duration_minute',
        'label' => 'Длительность, минуты',
        'value' => function(AiDialogRaw $raw) {
            return ceil($raw->duration / 60);
        }
    ],
    [
        'attribute' => 'duration',
        'label' => 'Длительность, сек',
        'class' => IntegerRangeColumn::class,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);

ActiveForm::end(); ?>