<?php
use \kartik\grid\GridView;
?>

<h2>Каналы продаж</h2>
<a href="/sale-channel/create">Добавить</a>

<div class="row">
    <div class="col-sm-12">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'label'=> (new $dataProvider->query->modelClass)->getAttributeLabel('name'),
                    'format' => 'raw',
                    'value'=>function ($data) {
                        return \yii\helpers\Html::a($data->name,'/sale-channel/edit?id='.$data->id);
                    },
                ],
                'dealer_id',
                'is_agent',
                'interest',
                'courierName',
            ],
        ]);
        ?>
    </div>
</div>