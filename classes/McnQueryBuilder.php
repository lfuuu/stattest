<?php

namespace app\classes;

use yii\db\Exception;
use yii\db\Expression;
use yii\db\pgsql\QueryBuilder;
use yii\db\Query;

/**
 * Class McnQueryBuilder
 *
 * @var Exception
 * @var Query
 * @var Expression
 *
 * @package app\classes
 */
class McnQueryBuilder extends QueryBuilder
{
    /**
     * Построить CTE-часть запроса
     *
     * @param array $queries
     * @param array $params
     *
     * @return string
     */
    public function buildWith($queries, &$params)
    {
        if (empty($queries)) {
            return '';
        }

        $tables = $this->_quoteWith($queries, $params);

        return 'WITH ' . implode(', ', $tables);
    }

    /**
     * Построить CTE-запросы
     *
     * @param array $queries
     * @param array $params
     *
     * @return array
     */
    private function _quoteWith($queries, & $params)
    {
        foreach ($queries as $i => $query) {
            if ($query instanceof Query) {
                list($sql, $params) = $this->build($query, $params);
                $queries[$i] = $this->db->quoteTableName($i) . " AS ($sql)";
            } elseif (is_string($i)) {
                if (strpos($query, '(') === false) {
                    $query = $this->db->quoteTableName($query);
                }
                
                $queries[$i] = $this->db->quoteTableName($i) . " AS $query";
            }
        }

        return $queries;
    }

    /**
     * Построение запроса из объекта класса Query
     *
     * @param Query $query
     * @param array $params
     *
     * @return array
     */
    public function build($query, $params = [])
    {
        $query = $query->prepare($this);

        $params = empty($params) ? $query->params : array_merge($params, $query->params);

        $clauses = [
            isset($query->with) ? $this->buildWith($query->with, $params) : null,
            $this->buildSelect($query->select, $params, $query->distinct, $query->selectOption),
            $this->buildFrom($query->from, $params),
            $this->buildJoin($query->join, $params),
            $this->buildWhere($query->where, $params),
            $this->buildGroupBy($query->groupBy),
            $this->buildHaving($query->having, $params),
        ];

        $sql = implode($this->separator, array_filter($clauses));
        $sql = $this->buildOrderByAndLimit($sql, $query->orderBy, $query->limit, $query->offset);

        if (!empty($query->orderBy)) {
            foreach ($query->orderBy as $expression) {
                if ($expression instanceof Expression) {
                    $params = array_merge($params, $expression->params);
                }
            }
        }

        if (!empty($query->groupBy)) {
            foreach ($query->groupBy as $expression) {
                if ($expression instanceof Expression) {
                    $params = array_merge($params, $expression->params);
                }
            }
        }

        $union = $this->buildUnion($query->union, $params);
        if ($union !== '') {
            $sql = "($sql){$this->separator}$union";
        }

        return [$sql, $params];
    }
}
