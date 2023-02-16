<?php

namespace app\widgets\GridViewExport;

use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\widgets\GridViewExport\Columns\Manager;
use app\widgets\GridViewExport\drivers\CsvDriver;
use app\widgets\GridViewExport\drivers\ExportDriver;
use Yii;
use yii\base\DynamicModel;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\base\UnknownPropertyException;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\grid\ActionColumn;
use yii\grid\Column;
use yii\grid\DataColumn;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\JsExpression;

class GridViewExport extends GridView
{
    const PARAM_CASE = 'case';
    const PARAM_PROPERTY = 'property';
    const COLUMN_CASE_SERIAL = 1;
    const COLUMN_CASE_ACTION = 2;
    const COLUMN_CASE_DEFAULT = 3;
    const COLUMN_CASE_DATA = 4;

    /** @var DataProviderInterface */
    public $dataProvider;
    /** @var \ActiveRecord\Model */
    public $filterModel;

    public $columns = [];
    public $batchSize = 1000;
    public $timeout = 0;
    public $columnSelectorEnabled = true;

    protected $id;
    protected $drivers = [
        'csv' => CsvDriver::class,
    ];

    /** @var DataProviderInterface */
    protected $provider = null;
    protected $visibleColumns = [];
    protected $columnRenders = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = Manager::me()->getSettings(get_class($this->filterModel), $this->columns);
        $this->columns = $settings->columns;

        parent::init();

        $this->provider = clone($this->dataProvider);
        if ($this->provider instanceof ActiveDataProvider && $this->provider->query instanceof ActiveQuery) {
            $this->provider->query->with($settings->eagerFields);
        }
    }

    /**
     * @return string
     * @throws BadRequestHttpException
     * @throws ExitException
     * @throws \ReflectionException
     */
    public function run()
    {
        $this->id = $this->getId();
        $this->registerPlugin();

        $actionRequest = Yii::$app->request->get('action');

        if (!empty($actionRequest)) {
            Yii::$app->controller->layout = false;

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $action = '_action' . ucfirst($actionRequest);

            if (!method_exists($this, $action)) {
                throw new BadRequestHttpException('Action not found');
            }

            $this->{$action}();

            Yii::$app->end();
        }

        $columns = [];

        foreach ($this->columns as $key => $column) {
            $label = $this->getColumnLabel($key, $column);
            $columns[] =
                Html::checkbox('export_gridview_columns[]', true, [
                    'data-key' => $key
                ]) .
                ' ' .
                $label;
        }

        $drivers = [];
        foreach ($this->drivers as $key => $driver) {
            $driverClass = new $driver;

            $drivers[] = [
                'label' => Html::tag('i', '', ['class' => 'text-info ' . $driverClass->icon]) . ' ' . $driverClass->name,
                'url' => '#',
                'linkOptions' => [
                    'data-export-gridview-format' => $key,
                    'data-uid' => $this->id,
                ],
            ];
        }

        return $this->render('_export', [
            'uid' => $this->id,
            'columns' => $columns,
            'drivers' => $drivers,
        ]);
    }

    /**
     * @param $key
     * @param Column $column
     * @return string
     * @throws \ReflectionException
     */
    protected function getColumnLabel($key, $column)
    {
        $label = Yii::t('export', 'Column') . ' ' . ($key + 1);

        if (!empty($column->label)) {
            $label = $column->label;
        } elseif (!empty($column->header)) {
            $label = $column->header;
        } elseif (!empty($column->attribute)) {
            $label = $this->getAttributeLabel($column->attribute);
        } elseif (!$column instanceof DataColumn) {
            $label = Inflector::camel2words((new \ReflectionClass($column))->getShortName());
        }

        return trim(strip_tags(preg_replace('#<br\s*\/?>#i', ' ', $label)));
    }

    /**
     * Fills column cases
     */
    protected function fillRenders()
    {
        $this->columnRenders = [];
        foreach ($this->visibleColumns as $columnIndex => $column) {
            $render = [];
            if ($column instanceof SerialColumn) {
                $render[self::PARAM_CASE] = self::COLUMN_CASE_SERIAL;
            } elseif ($column instanceof ActionColumn) {
                $render[self::PARAM_CASE] = self::COLUMN_CASE_ACTION;
                $render[self::PARAM_PROPERTY] = 0;
            } elseif ($column instanceof Column) {
                /** @var $column Column */
                $render[self::PARAM_CASE] = self::COLUMN_CASE_DEFAULT;
            } elseif (($column->content === null) && $column instanceof DataColumn) {
                $render[self::PARAM_CASE] = self::COLUMN_CASE_DATA;
            }

            $this->columnRenders[$columnIndex] = $render;
        }
    }

    /**
     * @param ActiveRecord $model
     * @param string $key
     * @param int $index
     * @return array
     * @throws InvalidParamException
     */
    public function getRow($model, $key, $index)
    {
        $row = [];

        foreach ($this->visibleColumns as $columnIndex => $column) {
            $render = $this->columnRenders[$columnIndex];
            switch ($render[self::PARAM_CASE]) {
                case self::COLUMN_CASE_SERIAL:
                    $value = $column->renderDataCell($model, $key, $index);
                    break;

                case self::COLUMN_CASE_ACTION:
                    $value = '';
                    break;

                case self::COLUMN_CASE_DEFAULT:
                    /** @var $column Column */
                    $value = strip_tags($column->renderDataCell($model, $key, $index));
                    break;

                case self::COLUMN_CASE_DATA:
                    /** @var $column DataColumn */
                    $format = 'raw';
                    if (isset($column->format)) {
                        if (is_array($column->format)) {
                            $column->format = $format;
                        }
                        $format = $column->format;
                    }

                    $value = $this->formatter->format($column->getDataCellValue($model, $key, $index), $format);
                    break;

                default:
                    $value = call_user_func($column->content, $model, $key, $index, $column);
            }

            if (empty($value) && !empty($column->attribute)) {
                if (!array_key_exists(self::PARAM_PROPERTY, $render)) {
                    try {
                        $result = ArrayHelper::getValue($model, $column->attribute, '');
                        $this->columnRenders[$columnIndex][self::PARAM_PROPERTY] = 1;
                        $value = $result;
                    } catch (UnknownPropertyException $e) {
                        $this->columnRenders[$columnIndex][self::PARAM_PROPERTY] = 0;
                    }
                } else if ($this->columnRenders[$columnIndex][self::PARAM_PROPERTY]) {
                    $value = ArrayHelper::getValue($model, $column->attribute, '');
                }
            }
            $row[] = strip_tags($value);
        }

        return $row;
    }

    /**
     * @param string $attribute
     * @return string
     */
    protected function getAttributeLabel($attribute)
    {
        /**
         * @var Model $model
         */
        $provider = $this->dataProvider;
        if ($provider instanceof ActiveDataProvider && $provider->query instanceof ActiveQueryInterface) {
            $model = new $provider->query->modelClass;
            return $model->getAttributeLabel($attribute);
        } else {
            $models = $provider->getModels();
            if (($model = reset($models)) instanceof Model) {
                return $model->getAttributeLabel($attribute);
            }
            return Inflector::camel2words($attribute);
        }
    }

    /**
     * @inheritdoc
     */
    protected function registerPlugin()
    {
        $view = $this->getView();

        GridViewExportAsset::register($view);

        $script = new JsExpression('
            jQuery("div[data-export-menu=\"' . $this->id . '\"]").gridViewMenu();
            jQuery("a[data-uid=\"' . $this->id . '\"]").gridViewDrivers({
                batchSize: ' . $this->batchSize . '
            });
        ');

        $view->registerJs($script);
    }

    private function _actionGetfile($key = null)
    {
        $_GET['key'] = $key ?: $this->getKey();
        $_GET['offset'] = 0;
        $_GET['batchSize'] = 1000000;

        $this->_actionInit($_GET['key']);
        $this->_actionIteration();
        $this->_actionDownload();

    }

    /**
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ModelValidationException
     * @throws \ReflectionException
     */
    private function _actionInit($key = null)
    {
        /** @var DynamicModel $input */
        $input = DynamicModel::validateData(
            Yii::$app->request->get(),
            [
                ['driver', 'string'],
                ['driver', 'required'],
                ['columns', ArrayValidator::class],
            ]
        );

        if ($input->hasErrors()) {
            throw new ModelValidationException($input);
        }

        /** @var ExportDriver $driver */
        $driver = new $this->drivers[$input->driver];

        $headerColumns = [];

        foreach ($input->columns as $column) {
            if (isset($this->columns[$column])) {
                $this->visibleColumns[] = $this->columns[$column];
                $headerColumns[] = $this->getColumnLabel($column, $this->columns[$column]);
            }
        }

        $key = $key ?: $this->getKey();

        if (($key = $driver->createHeader($key, $headerColumns)) === false) {
            throw new BadRequestHttpException('Cannot create export file');
        }

        \Yii::$app->response->content = Json::encode([
            'total' => $this->provider->getTotalCount(),
            'key' => $key,
        ]);
    }

    private function getKey()
    {

        if ($this->filterModel->hasProperty('connect_time_from') && $this->filterModel->hasProperty('connect_time_to')) {
            $keyData = [$this->filterModel->connect_time_from, $this->filterModel->connect_time_to];
        }
        foreach ($this->filterModel as $property => $value) {
            if ($value) {
                $keyData[] = substr($property, 0, 1) . (is_array($value) ? implode('_', $value) : $value);
            }
        }
        $name = StringHelper::basename(get_class($this->filterModel));
        $name = (($substr = strstr($name, 'Filter', true)) !== false) ? $substr : $name;
        $name = BaseInflector::underscore($name);

        return $name . '_' . implode('_', $keyData) . '_' . time();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @throws ModelValidationException
     * @throws BadRequestHttpException
     */
    private function _actionIteration()
    {
        /** @var DynamicModel $input */
        $input = DynamicModel::validateData(
            Yii::$app->request->get(),
            [
                [['driver', 'key'], 'string'],
                [['driver', 'key'], 'required'],
                [['batchSize', 'offset'], 'integer'],
                ['columns', ArrayValidator::class],
                ['offset', 'default', 'value' => 0],
            ]
        );

        if ($input->hasErrors()) {
            throw new \Exception(array_values($input->getFirstErrors())[0]);
        }

        if (!isset($this->drivers[$input->driver])) {
            throw new BadRequestHttpException('Cannot find driver "' . $input->driver . '"');
        }

        /** @var ExportDriver $driver */
        $driver = new $this->drivers[$input->driver];

        foreach ($input->columns as $column) {
            if (isset($this->columns[$column])) {
                $this->visibleColumns[] = $this->columns[$column];
            }
        }
        $this->fillRenders();

        $this->provider->pagination->pageSize = $this->batchSize;
        $this->provider->pagination->page = $input->offset;
        $this->provider->refresh();
        $models = array_values($this->provider->getModels());

        $keys = $this->provider->getKeys();
        $data = [];
        foreach ($models as $index => $model) {
            $data[] = $this->getRow($model, $keys[$index], $index);
        }
        $driver->setData($input->key, $data);

        \Yii::$app->response->content = Json::encode([
            'success' => true,
//            'total' => $this->provider->getTotalCount(),
            'iteration' => $input->offset,
        ]);
    }

    /**
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ModelValidationException
     * @throws HttpException
     */
    private function _actionDownload()
    {
        /** @var DynamicModel $input */
        $input = DynamicModel::validateData(
            Yii::$app->request->get(),
            [
                [['driver', 'key'], 'string'],
                [['driver', 'key'], 'required'],
            ]
        );

        if ($input->hasErrors()) {
            throw new ModelValidationException($input);
        }

        if (!isset($this->drivers[$input->driver])) {
            throw new BadRequestHttpException('Cannot find driver "' . $input->driver . '"');
        }

        /** @var ExportDriver $driver */
        $driver = new $this->drivers[$input->driver];
        Yii::$app->response->sendContentAsFile($driver->fetchFile($input->key, $deleteAfter = true), $input->key . $driver->extension);
    }

}
