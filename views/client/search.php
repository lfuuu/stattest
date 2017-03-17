<?php
use app\classes\grid\GridView;
use app\models\ClientAccount;
?>

<div class="row">
    <div class="col-sm-12">
        <?php
        /** @var ClientAccount $model */
        $model = new $dataProvider->query->modelClass;
        $labels = $model->attributeLabels();

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id' => [
                    'label' => $labels['id'],
                    'format' => 'raw',
                    'value' => function (ClientAccount $data) {
                        return '<a href="/client/view?id=' . $data->id . '">' . $data->getAccountTypeAndId() . '</a>';
                    },
                ],
                'contractNo',
                'status' => [
                    'label' => $labels['status'],
                    'format' => 'raw',
                    'value' => function (ClientAccount $data) {
                        return $data->contract->businessProcessStatus->color ?
                            '<b style="background:' . $data->contract->businessProcessStatus->color . ';">' . $data->contract->businessProcessStatus->name . '</b>' :
                            '<b>' . $data->contract->businessProcessStatus->name . '</b>';
                    },
                    'contentOptions' => function (ClientAccount $data) {
                        return $data->contract->businessProcessStatus->color ?
                            ['style' => 'background:' . $data->contract->businessProcessStatus->color] :
                            [];
                    }
                ],
                'companyName' => [
                    'label' => $labels['companyName'],
                    'format' => 'html',
                    'value' => 'companyName'
                ],
                'inn',
                'managerName',
                'channelName',
                'lastComment',
            ],
        ]);
        ?>
    </div>
</div>
