<?php
use \kartik\grid\GridView;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id' => [
            'label' => (new $dataProvider->query->modelClass)->attributeLabels()['id'],
            'format' => 'raw',
            'value' => function($data){
                return '<a href="/client/clientview?id='.$data->id.'">'.$data->id.'</a>';
            }
        ],
        'companyName',
        'inn',
        'managerName',
        'channelName',
    ],
]);
?>

<script>
    $('body').on('click', '.grid-view tbody tr', function(){
        location.href = '/client/clientview?id=' + $(this).data('key');
    });

    $('body').on('mouseover', '.grid-view tbody tr', function(){
        $(this).css('background', '#CCC');
    });

    $('body').on('mouseout', '.grid-view tbody tr', function(){
        $(this).css('background', '');
    });
</script>

<style>
    .grid-view tbody tr{
        cursor: pointer;
    }
</style>