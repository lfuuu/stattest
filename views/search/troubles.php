<?php
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\TroubleStage;

?>

<div class="row">
    <div class="col-sm-12">
        <?php
        /** @var TroubleStage $model */
        $model = new $dataProvider->query->modelClass;

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'trouble_id' => [
                    'label' => $model->getAttributeLabel('trouble_id'),
                    'format' => 'raw',
                    'value' => function (TroubleStage $data) {
                        return Html::a($data->trouble_id, $data->trouble->getUrl());
                    },
                ],
                'troubleComment' => [
                    'label' => 'Комментарий к заявке',
                    'format' => 'raw',
                    'value' => function(TroubleStage $data) {
                        return Html::tag(
                            'div',
                            $data->trouble->problem,
                            [
                                'class' => 'search-trouble-problem-button text-overflow-ellipsis',
                                'data-toggle' => 'popover',
                                'data-html' => 'true',
                                'data-placement' => 'bottom',
                                'data-content' => nl2br(htmlspecialchars($data->trouble->problem)),
                            ]
                        );
                    }
                ],
                'comment' => [
                    'label' => $model->getAttributeLabel('comment'),
                    'format' => 'raw',
                    'contentOptions' => [
                        'class' => 'search-trouble-column-comment',
                    ],
                    'value' => function(TroubleStage $stage) {
                        return Html::tag(
                            'div',
                            $stage->comment,
                            [
                                'class' => 'search-trouble-stage-comment-button text-overflow-ellipsis',
                                'data-toggle' => 'popover',
                                'data-html' => 'true',
                                'data-placement' => 'bottom',
                                'data-content' => nl2br(htmlspecialchars($stage->comment)),
                            ]
                        );
                    }

                ],
                'date_start',
                'user_main' => [
                    'label' => $model->getAttributeLabel('user_main'),
                    'value' => function(TroubleStage $stage) {
                        return $stage->user->name;
                    }
                ],
            ],
        ]);
        ?>
    </div>
</div>


<script>
  function ($) {
    'use strict';

    $(function () {
      var $popovers = $('[data-toggle="popover"]');
      $popovers.length && $popovers.popover();
    })

  }(jQuery);
</script>