<?php

namespace app\classes\model;

use app\classes\grid\column\DataColumn;
use app\exceptions\ModelValidationException;
use app\modules\nnp\models\FilterQuery;
use ReflectionClass;
use ReflectionProperty;
use yii\behaviors\AttributeTypecastBehavior;

class ActiveRecord extends \yii\db\ActiveRecord
{
    protected $isAttributeTypecastBehavior = false;

    /**
     * @return array
     */
    public function behaviors()
    {
        return
            $this->isAttributeTypecastBehavior ?
                [
                    'typecast' => [
                        'class' => AttributeTypecastBehavior::className(),
                        'typecastAfterValidate' => false,
                        'typecastAfterFind' => true,
                    ],
                ] :
                [];
    }


    /**
     * Подготавливает названия класса для работы с историей
     *
     * @return string
     */
    public function getClassName()
    {
        return get_class($this);
    }

    /**
     * @return array
     * @throws ModelValidationException
     */
    public function getFilterQueriesForAutocomplete()
    {
        return FilterQuery::find()
            ->select(['label' => 'name', 'value' => 'name', 'id'])
            ->where(['model_name' => $this->getClassName()])
            ->indexBy('id')
            ->asArray()
            ->all();
    }

    /**
     * Вернуть значения этого фильтра
     * getAttributes не подходит, ибо берет только поля БД, а нужно еще дополнительные из filter-модели
     *
     * @param array $except
     * @return array
     * @throws \ReflectionException
     */
    public function getObjectNotEmptyValues($except = [])
    {
        $values = [];
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            if ($property->isStatic()) {
                // не-статичные заранее нельзя отфильтровать. Поэтому фильтруем так
                continue;
            }

            $attribute = $property->getName();

            if (in_array($attribute, $except)) {
                continue;
            }

            $value = $this->$attribute;
            if ($value === null || $value === '') {
                // дефолтное значение. Сохранять не надо
                continue;
            }

            if (!$this->getFilterQueryAttributeLabel($attribute)) {
                // вероятно, служебное свойство от наследованного класса. Такие не надо сохранять
                continue;
            }

            $values[$attribute] = $value;
        }

        return $values;
    }

    /**
     * @param string $attribute
     * @return string
     */
    public function getFilterQueryAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        if (isset($labels[$attribute])) {
            // явно описанное свойство
            return $labels[$attribute];
        }

        if (strpos($attribute, '_from')) {
            // часть составного свойства
            $attribute = str_replace('_from', '', $attribute);
            return $this->getAttributeLabel($attribute) . ' (с)';
        }

        if (strpos($attribute, '_to')) {
            // часть составного свойства
            $attribute = str_replace('_to', '', $attribute);
            return $this->getAttributeLabel($attribute) . ' (по)';
        }

        // вероятно, служебное свойство от наследованного класса. Такие не надо сохранять
        return '';
    }

    /**
     * Если FK, то id заменить на красивое значение
     *
     * @param string $filterValueKey
     * @param mixed $filterValues
     * @param DataColumn[] $columns
     * @return mixed
     */
    public function getBeautyValue($filterValueKey, $filterValues, $columns)
    {
        if (is_array($filterValues)) {
            // если массив, то рекурсивно по всем элементам
            $filterBeautyValues = [];
            foreach ($filterValues as $filterValue) {
                $filterBeautyValues[] = $this->getBeautyValue($filterValueKey, $filterValue, $columns);
            }

            return $filterBeautyValues;
        }

        // найти колонку
        foreach ($columns as $column) {
            if (!isset($column->attribute) || $column->attribute != $filterValueKey) {
                continue;
            }

            // нашли нужную
            if ($column->filter && is_array($column->filter) && isset($column->filter[$filterValues])) {
                // ура!
                return $column->filter[$filterValues];
            }

            // это не FK
            // или в нем нет нужного значения. Хотелось бы вызвать renderDataCellContent, но он protected
            return $filterValues;
        }

        return $filterValues;

    }

    /**
     * Deletes rows in the table using the provided conditions.
     *
     * For example, to delete all customers whose status is 3:
     *
     * ```php
     * Customer::deleteAll('status = 3');
     * ```
     *
     * > Warning: If you do not specify any condition, this method will delete **all** rows in the table.
     *
     * Note that this method will not trigger any events. If you need [[EVENT_BEFORE_DELETE]] or
     * [[EVENT_AFTER_DELETE]] to be triggered, you need to [[find()|find]] the models first and then
     * call [[delete()]] on each of them. For example an equivalent of the example above would be:
     *
     * ```php
     * $models = Customer::find()->where('status = 3')->all();
     * foreach ($models as $model) {
     *     $model->delete();
     * }
     * ```
     *
     * For a large set of models you might consider using [[ActiveQuery::each()]] to keep memory usage within limits.
     *
     * @param string|array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
     * Please refer to [[Query::where()]] on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @param string $orderBy
     * @return int the number of rows deleted
     */
    public static function deleteAll($condition = null, $params = [], $orderBy = '')
    {
        $command = static::getDb()->createCommand();
        $command->delete(static::tableName(), $condition, $params);

        if ($orderBy) {
            $command->setSql($command->getRawSql() . ' ORDER BY ' . $orderBy);
        }

        return $command->execute();
    }
}