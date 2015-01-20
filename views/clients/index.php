<?php

use yii\grid\GridView;
use app\classes\grid\filters\FilterField;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
  
if(count($filters)> 0)
{
    $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
    ]);

    //var_dump($filters); exit();
    foreach($filters as $filter)
    {
        echo $filter->render();
    }

    echo Html::submitButton('Фильтровать', ['class' => 'btn btn-default btn-xs']);
    
    ActiveForm::end();
}

    $urlmanager = Yii::$app->getUrlManager();
    $url_params[0] = Yii::$app->controller->getRoute();

?>


    <ul class="nav nav-tabs">
     <?php foreach ($rows as $key => $value) { 
         $url_params['grid'] = $value['id'];
         $url_params['bp'] = $value['grid_business_process_id'];
         $link = $urlmanager->createUrl($url_params);
     ?>
        <li role="presentation" <?php if ( $value['id'] == $row['id'] ) { ?>class="active"<?php } ?>><a href="<?= $link ?>"><?= $value['name'] ?></a></li>
     <?php } ?>
    </ul>



<?php

if($dataProvider->totalCount > 0) {
    echo GridView::widget([
       'dataProvider' => $dataProvider,
       'columns' => $columns,
    ]);
}
