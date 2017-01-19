<?php
/**
 * Main page view for Raw report (/voip/raw)
 *
 * @var CallsRawFilter $filterModel
 * @var \yii\web\View $this
 */

use app\classes\grid\GridView;
use app\models\voip\filter\CallsRawFilter;
use yii\widgets\Breadcrumbs;
use app\classes\DateTimeWithUserTimezone;

?>

<?= app\classes\Html::formLabel($this->title = 'Отчет по данным calls_raw') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title],
    ],
]);

$aggrDigitCount = [
    'sale_sum' => 2,
    'sale_avg' => 2,
    'sale_min' => 2,
    'sale_max' => 2,
    'cost_price_sum' => 2,
    'cost_price_avg' => 2,
    'cost_price_min' => 2,
    'cost_price_max' => 2,
    'margin_sum' => 2,
    'margin_avg' => 2,
    'margin_min' => 2,
    'margin_max' => 2,
];

$filter = require '_indexFilters.php';

$columns = [];

if ($filterModel->group || $filterModel->group_period || $filterModel->aggr) {
    if ($filterModel->group_period) {
        $columns[] = [
            'label' => 'Интервал',
            'attribute' => 'interval',
        ];
    }

    if ($filterModel->group) {
        foreach ($filterModel->group as $value) {
            $columns[] = [
                'label' => $filterModel->groupConst[$value],
                'attribute' => $value
            ];
        }
    }

    $c = count($columns);
    foreach ($filterModel->aggr as $key => $value) {
        $columns[$key + $c] = [
            'label' => $filterModel->aggrLabels[$value],
            'attribute' => $value,
        ];
        if (strpos($value, 'session_time') !== false) {
            $columns[$key + $c]['value'] = function ($model) use ($value) {
                return DateTimeWithUserTimezone::formatSecondsToMinutesAndSeconds($model[$value]);
            };
        }

        if ($filterModel->currency_rate !== 1 && (strpos($value, 'sale') !== false || strpos($value, 'cost_price') !== false || strpos($value, 'margin') !== false)) {
            $columns[$key + $c]['value'] = function ($model) use ($value, $filterModel) {
                return $model[$value] / $filterModel->currency_rate;
            };
        }

        if (isset($aggrDigitCount[$value])) {
            $columns[$key + $c]['format'] = ['decimal', $aggrDigitCount[$value]];
        }
    }
} else {
    $columns = require '_indexColumns.php';
}

try {
    GridView::separateWidget(
        [
            'dataProvider' => $filterModel->getReport(),
            'filterModel' => $filterModel,
            'beforeHeader' => [
                'columns' => $filter
            ],
            'columns' => $columns,
            'pjaxSettings' => [
                'formSelector' => false,
                'linkSelector' => false,
                'enableReplaceState' => true,
                'timeout' => 180000,
            ],
            'filterPosition' => '',
            'emptyText' => isset($emptyText) ? $emptyText : ($filterModel->isFilteringPossible() ?
                Yii::t('yii', 'No results found.') :
                'Выберите время начала разговора и хотя бы еще одно поле'),
            'exportWidget' => \app\widgets\GridViewExport\GridViewExport::widget(
                [
                    'dataProvider' => $filterModel->getReport(),
                    'filterModel' => $filterModel,
                    'columns' => $columns,
                    'batchSize' => 5000,
                ]
            ),
        ]
    );
} catch (yii\db\Exception $e) {
    if ($e->getCode() == 8) {
        Yii::$app->session->addFlash(
            'error',
            'Запрос слишком тяжелый, чтобы выполниться. Задайте, пожалуйста, другие фильтры'
        );
    } else {
        Yii::$app->session->addFlash('error', "Ошибка выполнения запроса: " . $e->getMessage());
    }
}

list($serverUrl, $siteUrl) = Yii::$app->assetManager->publish(dirname(__FILE__) . '/assets');
$this->registerJsFile($siteUrl . '/index.js');

