<?php


namespace app\classes\grid;

use Yii;
use yii\db\ActiveQueryInterface;
use yii\base\Model;
use yii\db\Connection;
use yii\db\QueryInterface;
use yii\di\Instance;
use yii\data\ActiveDataProvider;
use app\classes\grid\filters\FilterField;
use yii\base\InvalidConfigException;

class FilterDataProvider extends ActiveDataProvider 
{
    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        if (($sort = $this->getSort()) !== false) {
            
            $order_fields = $sort->getOrders();
            
            foreach ($order_fields as $field => $sort )
            {
                $correct_order_fields[FilterField::QUERY_ALIAS.'.'.$field] = $sort; 
            }
           // var_dump($correct_order_fields); exit;
            $query->addOrderBy($correct_order_fields);
        }

        return $query->all($this->db);
    }

}
