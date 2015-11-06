<?php

use kartik\grid\GridView;
use app\classes\Html;

if (count($result)):
    echo GridView::widget([
        'id' => 'usage_lost_tariffs',
        'dataProvider' => $result,
        'columns' => [
            [
                'attribute' => 'number',
                'label' => 'Номер',
            ],
            [
                'attribute' => 'status',
                'label' => 'Статус',
                'format' => 'raw',
                'value' => function($data) {
                    if ($data->status == 'instock') {
                        return 'В продаже';
                    }
                    if ($data->status == 'active') {
                        return 'Используется';
                    }
                },
            ],
            [
                'label' => 'Результат',
                'format' => 'raw',
                'value' => function($data) {
                    if ($data->status == 'instock' && $data->usageVoip->id) {
                        return Html::tag('span', 'Есть активная услуга ID ' . $data->usageVoip->id, ['style' => 'color: red;']);
                    }
                    if ($data->status == 'active' && !$data->usageVoip->id) {
                        return Html::tag('span', 'Нет услуги', ['style' => 'color: blue;']);
                    }
                },
            ],
        ],
        'pjax' => true,
        'toolbar'=> [],
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'hover' => true,
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'Расхождения между базой учета номеров и статусом услуги',
        ],
    ]);
else: ?>
    <div class="well" style="text-align: center;">
        <div class="alert alert-info">
            Подозрительного не найдено
        </div>
    </div>
<?php endif; ?>