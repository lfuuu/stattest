<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\classes\yii;

use yii\grid\DataColumn;
use yii\helpers\Html;
use Yii;

class GlyphDataColumn extends DataColumn {   
    
    public $format = 'html';
    
    public function getDataCellValue($model, $key, $index)
    {
        //получение короткого соответствия для значения
        $value = parent::getDataCellValue($model, $key, $index);
        $cssclass = 'cell'.md5($value);
        
        $options = ['class' => $cssclass.' btn'];
        return Html::tag('span', '&nbsp;<!--'.$value.'-->', $options);//<!--
                        
    }
}

