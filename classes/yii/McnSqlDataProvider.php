<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\classes\yii;

use yii\data\SqlDataProvider;

class McnSqlDataProvider extends SqlDataProvider
{
   // в базовом классе не реализованна возможность поэтому пилю сам
   protected function prepareTotalCount()
    {
        $sql = 'select count(*) as totalcount from ('.$this->sql.') as q';
        return intval($this->db->createCommand($sql, $this->params)->queryScalar());
    }
    
}
