<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use yii\grid\GridView;
use app\classes\yii\McnSqlDataProvider;
use app\classes\yii\GlyphDataColumn;
use app\classes\yii\HrefDataColumn;
use app\models\ClientGrid;
use yii\helpers\Url;
use app\classes\Encoding;


class ClientsController extends BaseController
{
    public function actionIndex()
    {

        $dataset = ClientGrid::findOne(Yii::$app->request->get('grid'));

        if( count($dataset) == 0)
        {
            $dataset = ClientGrid::findDefault(Yii::$app->request->get('bp', 1));
        }

        $datasets = ClientGrid::findByBP($dataset->client_bp_id);

        $rows = $datasets;
        $row = $dataset->configAsArray;
        $row['sql'] = $dataset->sql;
        $row['id'] = $dataset->id;

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
 
        return  $this->render('index', [
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'rows' => $rows,
            'row' => $row
        ]);
    }
}