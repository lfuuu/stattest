<?php

namespace app\classes;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\db\QueryInterface;
use yii\db\QueryTrait;

/**
 * Class DBROQuery
 *
 * @info http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10420240
 * @package app\classes
 */
class DBROQuery extends Component implements QueryInterface
{
    use QueryTrait;

    const LIMIT_DEFAULT = 10000;

    private $from = "";
    private $fields = [];
    private $group = [];
    private $join = [];

    /** @var DBROConnection  */
    private $connection = null;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->connection = Yii::$app->dbroPlatforma;
    }

    public function select($columns)
    {
        if (is_array($columns)) {
            $newColumns = array_values($columns);
        } elseif (is_string($columns)) {
            $newColumns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $newColumns = [];
        }

        if ($newColumns) {
            $this->fields = array_unique(array_merge($this->fields + $newColumns));
        }

        return $this;
    }

    /**
     * Добавляем поля выборки
     *
     * @param string|array $fields. Добавляемые поля. Формат: ['поле1', 'поле2'] или 'поле1,поле2'
     * @return $this
     */
    public function addSelect($fields)
    {
        $this->select($fields);

        return $this;
    }

    /**
     * Устанавливаем таблицу выборки
     *
     * @param string $table Название таблицы
     * @return $this
     */
    public function from($table)
    {
        $this->from = $table;

        return $this;
    }

    /**
     * Подключение таблицы для JOIN'а
     *
     * @param string $table Название таблицы
     * @param $condition Условие подключения таблицы. Формат: ['tableFrom.id' => 'joinTable.join_id'] или ['=', 'tableFrom.id', 'joinTable.join_id']
     * @return $this
     * @throws InvalidParamException
     */
    public function join($table, $condition)
    {
        $this->join[] = ["with" => $table, "on" => $this->makeWhereQuery($condition)];

        return $this;
    }


    /**
     * Поля для групировки выборки
     *
     * @param array $columns Поля группировки. Формат: ['поле1', 'поле2'] или 'поле1,поле2'
     * @return $this
     */
    public function group($columns = [])
    {
        if (is_array($columns)) {
            $columns = array_values($columns);
        } else if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }

        if ($columns) {
            $this->group = $columns;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function fetchRows()
    {
        $query = $this->buildQuery();

        return $this->connection->executeQuery($query);
    }

    /**
     * Функция формирует запрос выборки на исполнение
     *
     * @return string
     * @throws InvalidParamException
     */
    protected function buildQuery()
    {
        $query = [
            'fields' => $this->fields,
            'from' => $this->from,
            'where' => $this->makeWhereQuery($this->where),
            'limit' => $this->limit,
            'group' => $this->group,
            'join' => $this->join
            ];

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function all($db = null)
    {
        if (is_null($this->limit)) {
            $this->limit = self::LIMIT_DEFAULT;
        }

        return $this->fetchRows();
    }

    /**
     * @inheritdoc
     */
    public function one($db = null)
    {
        $this->limit = 1;

        $rows = $this->fetchRows();

        return reset($rows);
    }

    /**
     * @inheritdoc
     */
    public function count($q = '*', $db = null)
    {
        return count($this->all($db));
    }

    /**
     * @inheritdoc
     */
    public function exists($db = null)
    {
        return !is_null($this->one($db));
    }

    /**
     * Функция конвертирует станадртную стурктуру условий выборки
     * в пригодную для использовани с DBRO
     *
     * Полная информация по доступных форматах доступна http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10420240
     *
     * @param array $condition Формат: 1. Полное совпадение ['поле' => 'значение']. 2 Общий формат ['оператор','поле', 'значение'].
     *                         Достпные операторы: '=', '>', '<', '>=', '<=', 'isnotnull', 'isnull','notin', 'in'
     *                         Условия можно объедениять функциями 'and' и 'or'. Для этого можно использовать  и стандарные Query-функции:
     *                              "andWhere", "orWhere"
     * @return array|mixed
     * @throws InvalidParamException
     */
    private function makeWhereQuery($condition)
    {
        if (!is_array($condition)) {
            return $condition;
        }

        if (!isset($condition[0])) {
            // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
            $result = [];
            foreach ($condition as $name => $value) {
                $result[] =["cond" =>  ["field" =>  $name, "cmp" => "=", "value" => $value]];
            }
            return count($result) == 1 ? $result[0] : ["and" => $result];
        }

        // operator format: operator, operand 1, operand 2, ...

        $operator = array_shift($condition);

        switch (strtoupper($operator)) {
            case '=';
            case '>';
            case '<';
            case '>=';
            case '<=';
                return ["cond" => ["field" => $condition[0], "cmp" => $operator, "value" => $condition[1]]];
                break;

            case 'ISNOTNULL';
            case 'ISNULL';
                return ["cond" => ["field" => $condition[0], "cmp" => $operator, "value" => ""]];
                break;

            case 'NOTIN':
            case 'IN':
                return [$operator => [ "field" => $condition[0], "values" => $condition[1] ]];
                break;

            case 'AND':
            case 'OR':
                $result = [];
                foreach ($condition as $operand) {
                    $result[] = $this->makeWhereQuery($operand);
                }
                return [$operator => $result];

                break;
        }
        throw new InvalidParamException("Ошибка разбора параметра where");
    }
}