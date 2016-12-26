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
     * Метод делает возможным задание для Query Builder'a
     * условия IN c именованным плейсхолдером (prepared statement)
     *
     * Например, $query->where(
     *      ['in', 'server_id', ':server_ids'],
     *      ['server_ids' => $this->server_ids]
     * )
     *
     * @param string $operator
     * @param array $operands
     * @param array $params
     *
     * @return string
     * @throws Exception
     */
    public function buildInCondition($operator, $operands, &$params)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new Exception("Operator '$operator' requires two operands.");
        }

        list($column, $values) = $operands;

        if ($column === []) {
            return $operator === 'IN' ? '0=1' : '';
        }

        if ($values instanceof Query) {
            return $this->buildSubqueryInCondition($operator, $column, $values, $params);
        }

        if ($column instanceof \Traversable || count($column) > 1) {
            return $this->buildCompositeInCondition($operator, $column, $values, $params);
        }

        if (is_array($column)) {
            $column = reset($column);
        }

        if (is_string($values) && strpos($values, ':') !== false) {
            $tmp = trim($values, ':');
            if (isset($params[$tmp])) {
                $values = $params[$tmp];
                unset($params[$tmp]);
            }
        }

        $sqlValues = [];
        foreach ($values as $i => $value) {
            if (is_array($value) || $value instanceof \ArrayAccess) {
                $value = isset($value[$column]) ? $value[$column] : null;
            }

            if ($value === null) {
                $sqlValues[$i] = 'NULL';
            } elseif ($value instanceof Expression) {
                $sqlValues[$i] = $value->expression;
                foreach ($value->params as $n => $v) {
                    $params[$n] = $v;
                }
            } else {
                $phName = isset($tmp) ? ":$tmp$i" : self::PARAM_PREFIX . count($params);
                $params[$phName] = $value;
                $sqlValues[$i] = $phName;
            }
        }

        if (empty($sqlValues)) {
            return $operator === 'IN' ? '0=1' : '';
        }

        if (strpos($column, '(') === false) {
            $column = $this->db->quoteColumnName($column);
        }

        if (count($sqlValues) > 1) {
            return "$column $operator (" . implode(', ', $sqlValues) . ')';
        } else {
            $operator = $operator === 'IN' ? '=' : '<>';
            return $column . $operator . reset($sqlValues);
        }
    }
}
