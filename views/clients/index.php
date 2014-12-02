<?php

use yii\grid\GridView;
use app\classes\yii\McnSqlDataProvider;
use app\classes\yii\GlyphDataColumn;
use app\classes\yii\HrefDataColumn;

use yii\helpers\Url;

use app\classes\yii\BaseController;
use app\classes\Encoding;

require(__DIR__ . '/../../controllers/config/grid.php');

$bp = Yii::$app->request->get('bp', 'telecom.accounting');
$bp = explode('.', $bp);
$filtered_rows = $menumap[$bp[0]][$bp[1]];

foreach ($filtered_rows as $filtered_row)
{
    if(isset($rows[$filtered_row]))
    {    
        $temp_rows[$filtered_row] = $rows[$filtered_row];
    }
}

$rows = $temp_rows;

$filtername = Yii::$app->request->get('status_filter');

if(!isset($rows[$filtername])) 
{
   $filtername = reset($filtered_rows);
}

$row = $rows[$filtername];

if(!isset($row)) return;

$dataProvider = new McnSqlDataProvider([
    'sql' => $row['sql'],
    //'totalcount' => 1000, 
    'sort' => [
        'attributes' => $row['sortable'],
        'defaultOrder' => $row['order'],
    ],
    'pagination' => [
        'pageSize' => $row['countperpage']
    ],
]);


$providerfields = array_keys(reset($dataProvider->getModels()));

foreach( $providerfields as $field)
{  
    
    foreach( $row['columns'][$field] as $key => $value )
    {
        $column[$key] = $value;
    }

    if(isset($column)&&!$column['hide']) 
    { 
        //нужно повторить названия полей провайдера для грида
        $column['attribute'] = $field;
        $columns[] = $column; 
    }
    
    unset( $label, $class, $column );
}

$urlmanager = Yii::$app->getUrlManager();
$url_params[0] = Yii::$app->controller->getRoute();

?>


    <ul class="nav nav-tabs">
     <?php foreach ($rows as $key => $value) { 
         $url_params['status_filter'] = $key;
         $url_params['bp'] = Yii::$app->request->get('bp');
         $link = $urlmanager->createUrl($url_params);
     ?>
        <li role="presentation" <?php if( $filtername == $key ) { ?>class="active"<?php } ?>><a href="<?= $link ?>"><?= $value['header'] ?></a></li>
     <?php } ?>
    </ul>



<?php

echo GridView::widget([
   'dataProvider' => $dataProvider,
   'columns' => $columns,
]);
