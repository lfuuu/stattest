<?php

use app\classes\grid\GridView;
use app\modules\sim\models\Registry;
use app\modules\sim\classes\RegistryState;
use app\modules\sim\filters\RegistryFilter;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;
use app\classes\Html;
use kartik\grid\ActionColumn;

$this->title = 'Реестр SIM-карт';
echo Html::formLabel($this->title);

/**
 * Реестр сим-карт
 *
 * @var app\classes\BaseView $this
 * @var RegistryFilter $filterModel
 */

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        $this->title = 'Импорт',
    ],
]) ?>

<?php
$baseView = $this;

$columns = [
    [
        'attribute' => 'region',
        'label' => 'Регион',
        'value' => function (Registry $simHistory) {
            return $simHistory->regionSettings->getRegionFullName();
        },
    ],
    [
        'attribute' => 'ICCID from',
        'label' => 'ICCID "c"',
        'format' => 'html',
        'value' => function (Registry $simHistory) {
            $settings = $simHistory->actualSettings;
            $html = sprintf('<span class="text-primary">%s</span><span class="text-success">%s</span><span class="text-danger">%s</span><span class="text-primary">%s</span>', $settings->iccid_prefix, $settings->iccid_region_code, $settings->iccid_vendor_code, $simHistory->getICCIDFromCut());

            return $html;
        },
    ],
    [
        'attribute' => 'ICCID to',
        'label' => 'ICCID "по"',
        'format' => 'html',
        'value' => function (Registry $simHistory) {
            $settings = $simHistory->actualSettings;
            $html = sprintf('<span class="text-primary">%s</span><span class="text-success">%s</span><span class="text-danger">%s</span><span class="text-primary">%s</span>', $settings->iccid_prefix, $settings->iccid_region_code, $settings->iccid_vendor_code, $simHistory->getICCIDToCut());

            return $html;
        },
    ],
    [
        'attribute' => 'count',
    ],
    [
        'attribute' => 'IMSI from',
        'label' => 'IMSI "c"',
        'format' => 'html',
        'value' => function (Registry $simHistory) {
            $settings = $simHistory->actualSettings;
            $html = sprintf('<span class="text-primary">%s</span><span class="text-secondary">%s</span><span class="text-success">%s</span>', $settings->imsi_prefix, $settings->imsi_region_code, $simHistory->getIMSIFromCut());

            return $html;
        },
    ],
    [
        'attribute' => 'IMSI to',
        'label' => 'IMSI "по"',
        'format' => 'html',
        'value' => function (Registry $simHistory) {
            $settings = $simHistory->actualSettings;
            $html = sprintf('<span class="text-primary">%s</span><span class="text-secondary">%s</span><span class="text-success">%s</span>', $settings->imsi_prefix, $settings->imsi_region_code, $simHistory->getIMSIToCut());

            return $html;
        },
    ],
    [
        'attribute' => 'imsi_s1_from',
    ],
    [
        'attribute' => 'imsi_s1_to',
    ],
    [
        'attribute' => 'imsi_s2_from',
    ],
    [
        'attribute' => 'imsi_s2_to',
    ],
    [
        'attribute' => 'state',
        'format' => 'html',
        'value' => function (Registry $simHistory) {
            $html = $simHistory->getStateName();

            $html .= '&nbsp;' . Html::tag('i', '', ['class' => $simHistory->getStateClass()]);

            return $html;
        },
    ],
    [
        'attribute' => 'created_at',
    ],
    [
        'attribute' => 'created_by',
        'value' => function (Registry $simHistory) {
            return $simHistory->createdBy->name;
        },
    ],
    [
        'attribute' => 'sim_type_id',
        'value' => function (Registry $simHistory) {
            return $simHistory->type->name;
        },
    ],
    [
        'class' => ActionColumn::class,
        'template' => '{cancel} {view} {start}',
        'buttons' => [
            'cancel' => function ($url, Registry $simHistory) use ($baseView) {
                if ($simHistory->state == RegistryState::NEW) {
                    return $baseView->render('//layouts/_link', [
                        'url' => '/sim/registry/cancel?id=' . $simHistory->id,
                        'glyphicon' => 'glyphicon-remove text-danger',
                        'params' => [
                            'onClick' => 'return confirm("Отменить заливку?")',
                        ],
                    ]);
                }
                if ($simHistory->state == RegistryState::CANCELLED) {
                    return $baseView->render('//layouts/_link', [
                        'url' => '/sim/registry/restore?id=' . $simHistory->id,
                        'glyphicon' => 'glyphicon-ok text-primary',
                        'params' => [
                            'onClick' => 'return confirm("Восстановить заливку?")',
                        ],
                    ]);
                }

                return '';
            },
            'view' => function ($url, Registry $simHistory) use ($baseView) {
                return $baseView->render('//layouts/_actionView', [
                    'url' => $simHistory->getUrl(),
                ]);
            },
            'start' => function ($url, Registry $simHistory) use ($baseView) {
                if ($simHistory->state == RegistryState::NEW) {
                    return $baseView->render('//layouts/_link', [
                        'url' => '/sim/registry/start?id=' . $simHistory->id,
                        'glyphicon' => 'glyphicon-play text-success',
                        'params' => [
                            'onClick' => 'return confirm("Запустить заливку?")',
                        ],
                    ]);
                }

                return false;
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'extraButtons' =>
        $this->render('//layouts/_buttonCreate', ['url' => '/sim/registry/add', 'name' => 'Создать заливку']),
    'isFilterButton' => false,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);

