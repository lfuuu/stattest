<?php

use app\classes\grid\GridView;
use app\classes\Html;
use app\models\Trouble;

?>

<div class="row">
    <div class="col-sm-12">
        <?php
        /** @var Trouble $model */
        $model = new $dataProvider->query->modelClass;

        echo GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id' => [
                        'label' => '№ заявки',
                        'format' => 'raw',
                        'value' => function (Trouble $data) {
                            return Html::a($data->id, $data->getUrl());
                        }
                    ],
                    'problem' => [
                        'label' => 'Проблема',
                        'format' => 'raw',
                        'value' => function (Trouble $data) {
                            return Html::tag(
                                'div',
                                $data->problem,
                                [
                                    'class' => 'search-trouble-problem-button text-overflow-ellipsis',
                                    'data-toggle' => 'popover',
                                    'data-html' => 'true',
                                    'data-placement' => 'bottom',
                                    'data-content' => nl2br(htmlspecialchars($data->problem)),
                                ]
                            );
                        }
                    ],
                    'roistat_visit' => [
                        'label' => 'Roistat visit',
                        'format' => 'raw',
                        'value' => function (Trouble $data) {
                            return Html::tag(
                                'div',
                                $data->troubleRoistat->roistat_visit
                            );
                        }
                    ],
                    'date_creation' => [
                        'label' => 'Дата создания',
                        'format' => 'raw',
                        'value' => function (Trouble $data) {
                            return Html::tag(
                                'div',
                                $data->date_creation
                            );
                        }
                    ]
                ]
            ]
        );
        ?>
    </div>
</div>

<script>
    +function ($) {
        'use strict';

        $(function () {
            var $popovers = $('[data-toggle="popover"]');
            $popovers.length && $popovers.popover();
        })

    }(jQuery);
</script>