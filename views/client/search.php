<?php
use \kartik\grid\GridView;
use app\classes\Html;
?>

<div class="row">
    <div class="col-sm-12">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id' => [
                    'label' => (new $dataProvider->query->modelClass)->attributeLabels()['id'],
                    'format' => 'raw',
                    'value' => function ($data) {
                        return '<a href="/client/view?id=' . $data->id . '">' . $data->id . '</a>';
                    },
                ],
                'contractNo',
                'status' => [
                    'label' => (new $dataProvider->query->modelClass)->attributeLabels()['status'],
                    'format' => 'raw',
                    'value' => function ($data) {
                        return '<b style="background:' . $data->contract->getBusinessProcessStatus()['color'] . ';">' . $data->contract->getBusinessProcessStatus()['name'] . '</b>';
                    },
                    'contentOptions' => function($data){ return ['style' => 'background:' . $data->contract->getBusinessProcessStatus()['color']];}
                ],
                'companyName' => [
                    'label' => (new $dataProvider->query->modelClass)->attributeLabels()['companyName'],
                    'format' => 'html',
                    'value' => 'companyName'
                ],
                'inn',
                'managerName',
                'channelName',
                'lastComment',
            ],
            'panelTemplate' =>
                Html::beginTag('div') .
                    Html::tag('div', '{summary}', ['class' => 'pull-left']) .
                    Html::tag('div', '{toolbar}', ['class' => 'pull-right', 'style' => 'margin-bottom: 10px;']) .
                    Html::tag('div', '', ['class' => 'clearfix']) .
                    '{items}{pager}' .
                Html::endTag('div'),
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
            ],
            'toolbar'=> [
                '{toggleData}',
            ]
        ]);
        ?>

        <script>
            $('body').on('click', '.grid-view tbody tr', function () {
                location.href = '/client/view?id=' + $(this).data('key');
            });

            $('body').on('mouseover', '.grid-view tbody tr', function () {
                $(this).addClass('tr-mouseover');
            });

            $('body').on('mouseout', '.grid-view tbody tr', function () {
                $(this).removeClass('tr-mouseover');
            });
        </script>

        <style>
            .grid-view tbody tr {
                cursor: pointer;
            }

            .tr-mouseover{
                background: #CCC !important;
            }
        </style>
    </div>
</div>
