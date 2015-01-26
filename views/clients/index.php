<?php
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use app\classes\grid\filters\FilterField;

/** @var FilterField[] $filters */
/** @var array $folders */

if (count($filters)> 0):
    $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
    ]);

    foreach($filters as $filter)
    {
        echo $filter->render();
    }

    echo Html::submitButton('Фильтровать', ['class' => 'btn btn-default btn-xs']);
    
    ActiveForm::end();
endif;

    $urlmanager = Yii::$app->getUrlManager();
    $url_params[0] = Yii::$app->controller->getRoute();

?>


    <ul class="nav nav-tabs">
     <?php foreach ($folders as $key => $folder):
         $url_params['grid'] = $folder['id'];
         $url_params['bp'] = $folder['grid_business_process_id'];
         $link = $urlmanager->createUrl($url_params);
     ?>
        <li role="presentation" <?php if ( $folder['id'] == $current['id'] ) { ?>class="active"<?php } ?>><a href="<?= $link ?>"><?= $folder['name'] ?></a></li>
     <?php endforeach; ?>
    </ul>



<?php

if($dataProvider->totalCount > 0) {
    echo GridView::widget([
       'dataProvider' => $dataProvider,
       'columns' => $columns,
    ]);
}
