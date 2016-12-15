<?php
/**
 * Main page view for CDR report (/voip/cdr)
 *
 * @var Cdr $filterModel
 * @var \yii\web\View $this
 */

use app\classes\grid\GridView;
use app\models\voip\filter\Cdr;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\CheckboxColumn;
use app\classes\grid\column\billing\ServiceTrunkColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\ContractColumn;
use app\classes\grid\column\billing\ReleasingPartyColumn;
use app\classes\grid\column\universal\NnpOperatorColumn;
use app\classes\grid\column\universal\NnpRegionColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;

?>

<?= app\classes\Html::formLabel($this->title = 'Отчет по данным calls_cdr') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title],
    ],
]);

$filter = [
    [
        'attribute' => 'server_id',
        'label' => 'Точка присоединения',
        'class' => ServerColumn::className(),
        'value' => $filterModel->server_id,
    ],
    [
        'attribute' => 'src_route',
        'label' => 'Транк-оригинатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByOperatorId' => $filterModel->src_operator_id,
        'filterByServerId' => $filterModel->server_id,
        'filterByContractId' => $filterModel->src_contract_id,
    ],
    [
        'attribute' => 'src_number',
        'label' => 'Маска номера А',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'dst_number',
        'label' => 'Маска номера В',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'setup_time',
        'label' => 'Время начала',
        'class' => DateTimeRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'src_contract_id',
        'label' => 'Договор номера А',
        'class' => ContractColumn::className(),
        'filterByTrunkName' => $filterModel->src_route,
    ],
    [
        'attribute' => 'src_operator_id',
        'label' => 'Оператор номера А',
        'class' => NnpOperatorColumn::className(),
    ],
    [
        'attribute' => 'dst_operator_id',
        'label' => 'Оператор номера В',
        'class' => NnpOperatorColumn::className(),
    ],
    [
        'attribute' => 'session_time',
        'label' => 'Длительность разговора',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'dst_route',
        'label' => 'Транк-терминатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByOperatorId' => $filterModel->dst_operator_id,
        'filterByContractId' => $filterModel->dst_contract_id,
        'filterByServerId' => $filterModel->server_id,
    ],
    [
        'attribute' => 'src_region_id',
        'label' => 'Регион номера А',
        'class' => NnpRegionColumn::className(),
    ],
    [
        'attribute' => 'dst_region_id',
        'label' => 'Регион номера B',
        'class' => NnpRegionColumn::className(),
    ],
    [
        'attribute' => 'call_id',
        'label' => 'call_id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'dst_contract_id',
        'label' => 'Договор номера B',
        'class' => ContractColumn::className(),
        'filterByTrunkName' => $filterModel->dst_route,
    ],
    [
        'attribute' => 'disconnect_cause',
        'label' => 'Код завершения',
        'class' => DisconnectCauseColumn::className(),
    ],
    [
        'attribute' => 'releasing_party',
        'label' => 'Инициатор завершения',
        'class' => ReleasingPartyColumn::className(),
    ],
    [
        'attribute' => 'redirect_number',
        'label' => 'Redirect number',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'is_success_calls',
        'label' => 'Только успешные попытки',
        'class' => CheckboxColumn::className(),
    ],
];

$columns = [
    [
        'label' => 'Идентификатор звонка',
        'attribute' => 'call_id',
        'enableSorting' => true,
        //'class' => IntegerColumn::className(),
    ],
    [
        'label' => 'Время начала',
        'attribute' => 'setup_time',
        //'class' => DateTimeRangeDoubleColumn::className(),
    ],
    [
        'label' => 'Длительность разговора',
        'attribute' => 'session_time',
        'enableSorting' => true,
        //'class' => IntegerColumn::className(),
    ],
    [
        'label' => 'Код завершения',
        'attribute' => 'disconnect_cause',
        'class' => DisconnectCauseColumn::className(),
    ],
    [
        'label' => 'Номер А',
        'attribute' => 'src_number',
        //'class' => StringColumn::className(),
    ],
    [
        'label' => 'Оператор',
        'attribute' => 'src_operator_name',
        //'class' => NnpOperatorColumn::className(),
    ],
    [
        'label' => 'Регион',
        'attribute' => 'src_region_name',
        //'class' => NnpRegionColumn::className(),
    ],
    [
        'label' => 'Номер В',
        'attribute' => 'dst_number',
        //'class' => StringColumn::className(),
    ],
    [
        'label' => 'Оператор',
        'attribute' => 'dst_operator_name',
        //'class' => NnpOperatorColumn::className(),
    ],
    [
        'label' => 'Регион',
        'attribute' => 'dst_region_name',
        //'class' => NnpRegionColumn::className(),
    ],
    [
        'label' => 'Redirect number',
        'attribute' => 'redirect_number',
        //'class' => StringColumn::className(),
    ],
    [
        'label' => 'Транк-оригинатор',
        'attribute' => 'src_route',
        //'class' => ServiceTrunkColumn::className(),
    ],
    [
        'label' => 'Договор',
        'attribute' => 'src_contract_name',
        //'class' => ContractColumn::className(),
    ],
    [
        'label' => 'Транк-терминатор',
        'attribute' => 'dst_route',
        //'class' => ServiceTrunkColumn::className(),
    ],
    [
        'label' => 'Договор',
        'attribute' => 'dst_contract_name',
        //'class' => ContractColumn::className(),
    ],
    [
        'label' => 'Инициатор завершения',
        'attribute' => 'releasing_party',
        //'class' => ReleasingPartyColumn::className(),
    ],
    [
        'label' => 'Время соединения',
        'attribute' => 'connect_time',
    ],
    [
        'label' => 'ПДД',
        'attribute' => 'pdd',
    ],
];

Pjax::begin([
    'formSelector' => false,
    'linkSelector' => false,
    'enableReplaceState' => true,
    'timeout' => 180000,
]);

echo GridView::widget([
    'dataProvider' => $filterModel->getReport(),
    'filterModel' => $filterModel,
    'beforeHeader' => [
        'columns' => $filter
    ],
    'columns' => $columns,
    'pjax' => true,
    'filterPosition' => '',
]);
Pjax::end();

?>