<?php
use \kartik\grid\GridView;
use \yii\helpers\Html;
use \app\models\ClientContract;
?>

<h2>Отчет по файлам</h2>
<div class="row">
    <div class="col-sm-12">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'label'=> (new ClientContract())->getAttributeLabel('number'),
                    'format' => 'raw',
                    'value' => function($data){
                        return Html::a($data->contract->number, '/contract/view?id='.$data->contract_id);
                    },
                ],
                [
                    'label'=> (new $dataProvider->query->modelClass)->getAttributeLabel('companyName'),
                    'format' => 'raw',
                    'value' => function($data){
                        return $data->contract->contragent->name;
                    },
                ],
                [
                    'label'=> (new $dataProvider->query->modelClass)->getAttributeLabel('filename'),
                    'format' => 'raw',
                    'value' => function($data){
                        return Html::a($data->contract->number, '/file/download?id='.$data->id);
                    },
                ],
                'comment',
                [
                    'label'=> (new $dataProvider->query->modelClass)->getAttributeLabel('user'),
                    'format' => 'raw',
                    'value' => function($data){
                        return $data->user->name;
                    },
                ],
                'ts',
                [
                    'label' => (new ClientContract())->getAttributeLabel('manager'),
                    'format' => 'raw',
                    'value' => function($data){
                        return $data->contract->getManagerName();
                    },
                ],
            ],
        ]);
        ?>
    </div>
</div>