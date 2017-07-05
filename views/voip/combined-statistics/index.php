<?php
/**
 * Представление для отчета по объединенной статистике звонков 4-го и 5-го классов
 *
 * @var CombinedStatistics $filterModel
 * @var \app\classes\BaseView $this
 */

use app\classes\grid\GridView;
use app\models\voip\filter\CombinedStatistics;
use yii\widgets\Breadcrumbs;
use app\classes\grid\column\DateRangePickerColumn;
use app\classes\Html;

?>

<?= app\classes\Html::formLabel($this->title = 'Статистика (5 класс + 4 класс)') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title],
    ],
]);

$filter = [
    [
        'attribute' => 'date',
        'name' => 'date',
        'label' => 'Период',
        'class' => DateRangePickerColumn::className(),
        'value' => $filterModel->date,
    ],
];

$td = Html::tag('td', 'Пятый класс', ['colspan' => 7]);
$tr = Html::tag('tr', $td);

$td = Html::tag('td', 'Стартовый объект');
$td .= Html::tag('td', 'Целевой номер');
$td .= Html::tag('td', 'Статус');
$td .= Html::tag('td', 'Время соединения');
$td .= Html::tag('td', 'Время разъединения');
$td .= Html::tag('td', 'Тип объекта');
$td .= Html::tag('td', 'IP узла');

$tr .= Html::tag('tr', $td);

$fiveClassHeader = Html::tag('table', $tr, ['class' => 'five_class_table']);

try {
    echo GridView::widget(
        [
            'dataProvider' => $filterModel->getStatistics(),
            'filterModel' => $filterModel,
            'beforeHeader' => [
                'columns' => $filter
            ],
            'columns' => [
                [
                    'label' => 'Четвертый класс',
                    'value' => function ($model) {
                        $content = $value = '';
                        $nnp = [];
                        if ($model['src_number_first']) {
                            $content = $model['src_number_first'];

                            if ($model['src_operator_name_first']) {
                                $content .= ' - ' . $model['src_operator_name_first'];
                            }

                            if ($model['src_country_name_first']) {
                                $nnp[] = $model['src_country_name_first'];
                            }

                            if ($model['src_region_name_first']) {
                                $nnp[] = $model['src_region_name_first'];
                            }

                            if ($model['src_city_name_first']) {
                                $nnp[] = $model['src_city_name_first'];
                            }

                            if ($nnp) {
                                $content .= '(' . implode(', ', $nnp) . ')';
                            }

                            $content .= '  →  ';

                            $nnp = [];
                            if ($model['dst_number_first']) {
                                $content .= $model['dst_number_first'];
                            }

                            if ($model['dst_operator_name_first']) {
                                $content .= ' - ' . $model['src_operator_name_first'];
                            }

                            if ($model['dst_country_name_first']) {
                                $nnp[] = $model['src_country_name_first'];
                            }

                            if ($model['dst_region_name_first']) {
                                $nnp[] = $model['src_region_name_first'];
                            }

                            if ($model['dst_city_name_first']) {
                                $nnp[] = $model['src_city_name_first'];
                            }

                            if ($nnp) {
                                $content .= '(' . implode(', ', $nnp) . ')';
                            }
                        }

                        if ($content) {
                            $value = Html::tag('span', $content, ['class' => 'incoming']);
                            $value .= Html::tag('br');
                        }

                        $content = '';
                        $nnp = [];
                        if ($model['src_number_last']) {
                            $content = $model['src_number_last'];

                            if ($model['src_operator_name_last']) {
                                $content .= ' - ' . $model['src_operator_name_last'];
                            }

                            if ($model['src_country_name_last']) {
                                $nnp[] = $model['src_country_name_last'];
                            }

                            if ($model['src_region_name_last']) {
                                $nnp[] = $model['src_region_name_last'];
                            }

                            if ($model['src_city_name_last']) {
                                $nnp[] = $model['src_city_name_last'];
                            }

                            if ($nnp) {
                                $content .= '(' . implode(', ', $nnp) . ')';
                            }

                            $content .= '  →  ';

                            $nnp = [];
                            if ($model['dst_number_last']) {
                                $content .= $model['dst_number_last'];
                            }

                            if ($model['dst_operator_name_last']) {
                                $content .= ' - ' . $model['src_operator_name_last'];
                            }

                            if ($model['dst_country_name_last']) {
                                $nnp[] = $model['src_country_name_last'];
                            }

                            if ($model['dst_region_name_last']) {
                                $nnp[] = $model['src_region_name_last'];
                            }

                            if ($model['dst_city_name_last']) {
                                $nnp[] = $model['src_city_name_last'];
                            }

                            if ($nnp) {
                                $content .= '(' . implode(', ', $nnp) . ')';
                            }
                        }

                        if ($content) {
                            $value .= Html::tag('span', $content, ['class' => 'outgoing']);
                        }

                        return $value;
                    },
                    'format' => 'html',
                    'contentOptions' => ['class' => 'pre'],
                ],
                [
                    'label' => 'Тип',
                    'value' => function ($model) {
                        return '<span class="' . ($model['vpbx_id'] ? 'vpbx' : 'phone') . '"></span>';
                    },
                    'format' => 'html',
                    'contentOptions' => ['style' => 'vertical-align: middle;'],
                ],
                [
                    'header' => $fiveClassHeader,
                    'format' => 'html',
                    'value' => function ($model) {
                        $fiveClass = json_decode($model['five_class'], true);
                        $tr = '';
                        $objectKindPrev = $trClass = $prevTime = null;
                        foreach ($fiveClass AS $value) {
                            $value['connect_time'] = str_replace('T', ' ', $value['connect_time']);
                            $value['disconnect_time'] = str_replace('T', ' ', $value['disconnect_time']);
                            $objectKind = $value['object_kind'] == 'queue' ? "Очередь {$value['src_number']}" : "Абонент {$value['src_number']}";
                            if ($objectKindPrev && $objectKindPrev == $objectKind && $value['object_kind'] != 'queue' && $prevTime != $value['connect_time']) {
                                $trClass = ['class' => 'block'];
                            }

                            $td = Html::tag('td', $objectKind);
                            $td .= Html::tag('td', $value['dst_number']);
                            $td .= Html::tag('td', $value['status']);
                            $td .= Html::tag('td', $value['connect_time']);
                            $td .= Html::tag('td', $value['disconnect_time']);
                            $td .= Html::tag('td', $value['object_type']);
                            $td .= Html::tag('td', $value['sip_ip']);

                            $tr .= Html::tag('tr', $td, $trClass);

                            $objectKindPrev = $objectKind;
                            $prevTime = $value['connect_time'];
                        }

                        return Html::tag('table', $tr, ['class' => 'five_class_table']);
                    },
                    'contentOptions' => [
                        'style' => 'padding: 0'
                    ],
                    'headerOptions' => [
                        'style' => 'padding: 0'
                    ]
                ],
            ],
            'filterPosition' => '',
            'emptyText' => $filterModel->dateStart && $filterModel->dateEnd && $filterModel->account_id ?
                Yii::t('yii', 'No results found.') :
                'Выберите период времени начала разговора и клиента',
        ]
    );

} catch (yii\db\Exception $e) {
    if ($e->getCode() == 8) {
        Yii::$app->session->addFlash(
            'error',
            'Запрос слишком тяжелый, чтобы выполниться. 
             Задайте, пожалуйста, другие фильтры'
        );
    } else {
        Yii::$app->session->addFlash('error', "Ошибка выполнения запроса: " . $e->getMessage());
    }
}

?>

