<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\classes\yii;

use yii\grid\DataColumn;
use Yii;

class HrefDataColumn extends DataColumn {
    
    public $linkpattern = '<a href="{link}">{value}</a>';
    
    public $href;
    
    public $format = 'html';
    
    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        if($value == null) return;
        
        $fields = array_keys($model); 
        $href = $this->href;
    
        foreach( $fields as $modelfield )
        {
          $href = str_replace('{'.$modelfield.'}', $model[$modelfield], $href);  
        }

        
        $params[0] = $href;//Yii::$app->controller->getRoute();     
        $url = Yii::$app->getUrlManager()->createAbsoluteUrl($params);
        
        $link = $this->linkpattern; 
        $link = str_replace( '{link}', $url, $link);
        $link = str_replace( '{value}', $value, $link);
        
        return $link;                    
    }
}

