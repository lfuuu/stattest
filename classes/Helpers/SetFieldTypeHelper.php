<?php

namespace app\classes\helpers;


use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Exception;

class SetFieldTypeHelper
{

    public static function generateCondition(ActiveRecord $model, $fieldName, $values, $separator = 'AND', $not = false)
    {
        if (!is_array($values))
            $values = [$values];

        $conditions = [];
        $values = array_intersect(self::getValidValues($model, $fieldName), $values);

        foreach ($values as $value) {
            $queryString = "FIND_IN_SET('{$value}', `{$fieldName}`)";
            if ($not)
                $queryString = 'NOT ' . $queryString;
            $conditions[] = $queryString;
        }
        return implode(" $separator ", $conditions);
    }

    public static function getFieldValue(ActiveRecord $model, $fieldName)
    {
        return self::pareseValue($model->$fieldName);
    }

    public static function generateFieldValue(ActiveRecord $model, $fieldName, array $values, $validate = true)
    {
        if ($validate && !self::validateField($model, $fieldName, $values)) {
            return false;
        }

        $resValues = [];
        foreach (self::getValidValues($model, $fieldName) as $value) {
            if(in_array($value, $values))
                $resValues[] = $value;
        }

        return implode(',', $resValues);
    }

    public static function validateField(ActiveRecord $model, $fieldName, $values, $setErrorTo = null)
    {
        if($setErrorTo === null || !($setErrorTo instanceof Model))
            $setErrorTo = $model;

        $validValues = self::getValidValues($model, $fieldName);

        if (!is_array($values)) {
            if(strpos($values, ',') === false)
                $values = [$values];
            else
                $values = self::pareseValue($values);
        }

        $diffs = array_diff($values, $validValues);

        if (count($diffs) == 0)
            return true;

        foreach ($diffs as $diff) {
            $setErrorTo->addError($fieldName, "Value \"{$diff}\" incorrect ");
        }

        return false;
    }

    private static function pareseValue($value)
    {
        return explode(',', $value);
    }

    private static function getValidValues(ActiveRecord $model, $fieldName)
    {
        $column = $model->getTableSchema()->getColumn($fieldName);
        if (!$column)
            throw new Exception('Field "' . $fieldName . '" does not exists');

        $type = $column->dbType;

        if (substr($type, 0, 4) != 'set(')
            throw new Exception("Field type '{$fieldName}' is not a 'SET'");

        return array_merge([''], array_map(
            function ($val) {
                return trim($val, "'");
            },
            explode(',', substr($type, 4, strlen($type) - 5))
        ));
    }
}