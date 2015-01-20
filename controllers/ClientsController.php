<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use yii\grid\GridView;
use app\classes\yii\McnSqlDataProvider;
use app\classes\yii\GlyphDataColumn;
use app\classes\yii\HrefDataColumn;
use app\models\ClientGridSettings;
use yii\helpers\Url;
use app\classes\Encoding;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use app\classes\grid\filters\FilterField;



class ClientsController extends BaseController
{
    public function actionIndex()
    {   
        $dataset = ClientGridSettings::findOne(Yii::$app->request->get('grid'));  

        if( count($dataset) == 0) 
        {
            $dataset = ClientGridSettings::findDefault(Yii::$app->request->get('bp', 1));
        }

        $datasets = ClientGridSettings::findByBP($dataset->grid_business_process_id);

        $rows = $datasets;
        $row = $dataset->configAsArray;
        $row['sql'] = $dataset->sql;
        $row['id'] = $dataset->id;

        foreach ($row['order'] as $key => $value)
        { 
          unset($row['order'][$key]);
          $row['order'][FilterField::QUERY_ALIAS.'.'.$key] = $value;
        }
        
        $query = new Query;
        $query->from('('.$row['sql'].') as '.FilterField::QUERY_ALIAS);
        
        $filters = $row['filter'];
        
        foreach ($filters as $filter)
        {
           $config['class'] = $filter['classname'];
           $config['query'] = $query;
           $rendered_filters[] = Yii::createObject($config); 
        }
        
        $dataProvider = new ActiveDataProvider([
            //'sql' => $row['sql'],
            //'totalcount' => 1000, 
            
            'query' => $query,
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
            'row' => $row,
            'filters' => $rendered_filters,
        ]);
    }
}