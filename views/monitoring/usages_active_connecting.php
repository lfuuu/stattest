<?php

use app\classes\Html;
use kartik\grid\GridView;
use app\helpers\DateTimeZoneHelper;

if (count($result)):
    echo GridView::widget([
        'id' => 'missing_manager',
        'dataProvider' => $result,
        'columns' => [
            [
                'attribute' => 'id',
                'label' => 'ID услуги',
            ],
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a(
                        $data->clientAccount->contract->contragent->name . ' / Договор № ' . $data->clientAccount->contract->number . ' / ЛС № ' . $data->clientAccount->id,
                        ['/client/view', 'id' => $data->clientAccount->id],
                        [
                            'target' => '_blank',
                        ]
                    );
                },
            ],
            [
                'label' => 'Тип услуги',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getTransferHelper($data)->getTypeTitle();
                }
            ],
            [
                'label' => 'Актуальность',
                'format' => 'raw',
                'value' => function($data) {
                    return
                        DateTimeZoneHelper::getDateTime($data->actual_from)
                        . ' -> ' .
                        DateTimeZoneHelper::getDateTime($data->actual_to);
                },
            ],
            [
                'label' => 'Описание услуги',
                'format' => 'raw',
                'value' => function($data) {
                    list ($title, $description, $other) = (array) $data->getTransferHelper($data)->getTypeDescription();
                    return $title . ' ' . $description;
                }
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
            'heading' => 'Список активных услуг со статусом "подключаемые"',
        ],
    ]);
else: ?>
    <div class="well" style="text-align: center;">
        <div class="alert alert-info">
            Подозрительного не найдено
        </div>
    </div>
<?php endif; ?>