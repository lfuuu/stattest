<?php
namespace app\classes\grid;
use Yii;

class Query extends \yii\db\Query
{
    public $subQuery;
    
    public function createCommand($db = null)
    {
        if ($db === null) {
            $db = Yii::$app->getDb();
        }
        
        list ($sql, $params) = $db->getQueryBuilder()->build($this);
        
        $sql = str_replace('SUB_QUERY', $this->subQuery, $sql);
        
        return $db->createCommand($sql, $params);
    }
}
