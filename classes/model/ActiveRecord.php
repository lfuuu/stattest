<?php

namespace app\classes\model;

use app\classes\grid\column\DataColumn;
use app\exceptions\ModelValidationException;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Country;
use app\models\Organization;
use app\modules\nnp\models\FilterQuery;
use app\modules\uu\models\Tag;
use app\modules\uu\models\Tariff;
use ReflectionClass;
use ReflectionProperty;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\Connection;

class ActiveRecord extends \yii\db\ActiveRecord
{
    const PG_ACCOUNT_TIMEOUT = 1000;
    const PG_DEFAULT_TIMEOUT = 3000;
    const PG_CALCULATE_RESOURCE_TIMEOUT = 600000;

    protected $isAttributeTypecastBehavior = false;

    private static $lastPgTimeout = 0;

    /** @var array Данные для записи в лог при удалении. Например, на кого перемапили */
    public $newHistoryData;

    /**
     * @return array
     */
    public function behaviors()
    {
        return
            $this->isAttributeTypecastBehavior ?
                [
                    'typecast' => [
                        'class' => AttributeTypecastBehavior::class,
                        'typecastAfterValidate' => false,
                        'typecastAfterFind' => true,
                    ],
                ] :
                [];
    }

    /**
     * Вернуть класс + ID
     *
     * @param self|self[] $models Одна или массив моделей, которые надо искать
     * @param array $parentModel Исходная модель (можно свежесозданную и несохраненную) и id родителя
     * @param string $idField
     * @return string
     */
    public static function getHistoryIds($models, $parentModel = [], $idField = 'id')
    {
        if (!is_array($models)) {
            if ($models) {
                $models = [$models];
            } else {
                $models = [];
            }
        }

        $historyIdPhp = [];
        foreach ($models as $model) {
            if ($model->isNewRecord) {
                continue;
            }

            $historyIdPhp[] = [$model->getClassName(), (string)$model->{$idField}, 0];
        }

        if (count($parentModel) === 2) {
            list($model, $fieldValue) = $parentModel;
            $historyIdPhp[] = [$model->getClassName(), 0, $fieldValue];
        }

        $historyIdJson = json_encode($historyIdPhp);
        $historyIdJson = str_replace('"', "'", $historyIdJson); // чтобы не конфликтовать с кавычками html-атрибута
        return $historyIdJson;
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
     * @param array $except за исключением полей
     * @param bool $isSkipEmpty пропускать пустые значения
     * @return array
     * @throws \ReflectionException
     */
    public function getObjectNotEmptyValues($except = [], $isSkipEmpty = true)
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

            $value = $this->{$attribute};
            if ($isSkipEmpty && ($value === null || $value === '')) {
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

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        if (strpos($field, 'is_') !== false) {
            return self::prepareHistoryBoolValue($value);
        }

        if (substr($field, -4) === '_utc') {
            return self::prepareHistoryDateTimeUtcValue($value);
        }

        return self::humanizedHistory($field, $value);
    }

    public static function prepareHistoryBoolValue($value)
    {
        return $value ? 'Да' : 'Нет';
    }

    public static function prepareHistoryDateTimeUtcValue($dateStr)
    {
        return (new \app\classes\DateTimeWithUserTimezone($dateStr))->getDateTime();
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return [];
    }

    /**
     * Вернуть ID родителя
     *
     * @return int
     */
    public function getParentId()
    {
        return null;
    }

    /**
     * Установить ID родителя
     *
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
    }


    /**
     * Установить timeout на любой запрос к billing
     *
     * @param int $timeout
     * @param Connection $db
     * @throws \yii\db\Exception
     */
    public static function setPgTimeout($timeout = self::PG_DEFAULT_TIMEOUT, $db = null)
    {
        $timeout = (int)$timeout;
        if (self::$lastPgTimeout === $timeout) {
            // уже устанавливали, повторно не надо
            return;
        }

        self::$lastPgTimeout = $timeout;

        if (!$db) {
            $db = static::getDb();
        }

        $db->createCommand('SET statement_timeout TO ' . $timeout)
            ->execute();
    }

    /**
     * Получаем максимальный Идентификатор модели
     *
     * @return mixed
     */
    public static function getMaxId()
    {
        return self::find()->max('id');
    }

    /**
     * @param array $data
     * @return string
     */
    public static function humanizedHistory($key, $value)
    {
        switch ($key) {
            case 'package_id':
                if ($tariff = Tariff::findOne(['id' => $value])) {
                    return $tariff->name;
                }
                break;
            case 'organization_id':
                if (
                    $organization = Organization::find()
                        ->where([Organization::tableName() . '.organization_id' => $value])
                        ->actual()
                        ->one()
                ) {
                    return $organization->name->value;
                }
                break;
            case 'federal_district':
                if (isset(ClientContract::$districts[$value])) {
                    return ClientContract::$districts[$value];
                }
                break;
            case 'business_id':
                if ($business = Business::findOne(['id' => $value])) {
                    return $business->name;
                }
                break;
            case 'business_process_id':
                if ($businessProcess = BusinessProcess::findOne(['id' => $value])) {
                    return $businessProcess->name;
                }
                break;
            case 'business_process_status_id':
                if ($businessProcessStatus = BusinessProcessStatus::findOne(['id' => $value])) {
                    return $businessProcessStatus->name;
                }
                break;
            case 'country_id':
                if ($country = Country::findOne(['code' => $value])) {
                    return $country->name;
                }
                break;
            case 'contragent_id':
                if ($contragent = ClientContragent::findOne(['id' => $value])) {
                    return $contragent->name;
                }
                break;
            case 'tag_id':
                if ($tag = Tag::findOne(['id' => $value])) {
                    return $tag;
                } else {
                    return '???#' . $value;
                }
                break;
            case 'tariff_id':
                if ($tariff = Tariff::findOne(['id' => $value])) {
                    return $tariff;
                } else {
                    return '???#' . $value;
                }
                break;

            default:
                return $value;
        }

        return $value;
    }

    /**
     * Пакетная вставка массива моделей
     *
     * @param ActiveRecord[] $models
     * @return int
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     */
    public static function batchInsertModels(array $models)
    {
        if (empty($models)) {
            return 0;
        }

        $rows = [];
        foreach ($models as $model) {
            if (!$model->validate()) {
                // At least one model has invalid data
                throw new ModelValidationException($model);
            }

            $rows[] = $model->attributes;
        }

        return $model
            ->getDb()
            ->createCommand()
            ->batchInsert(
                $model->tableName(),
                $model->attributes(),
                $rows
            )->execute();
    }
}
