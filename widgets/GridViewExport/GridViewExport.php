<?php

namespace app\widgets\GridViewExport;

use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\widgets\GridViewExport\drivers\CsvDriver;
use app\widgets\GridViewExport\drivers\ExportDriver;
use Yii;
use yii\base\DynamicModel;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\grid\ActionColumn;
use yii\grid\Column;
use yii\grid\DataColumn;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\JsExpression;

class GridViewExport extends GridView
{

    public
        $dataProvider,
        $filterModel,
        $columns = [],
        $batchSize = 1000,

        $timeout = 0,
        $columnSelectorEnabled = true;

    private
        $id,
        $drivers = [
            'csv' => CsvDriver::class,
        ],
        $provider = null,
        $visibleColumns = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->provider = clone($this->dataProvider);
    }

    /**
     * @return string
     * @throws BadRequestHttpException
     * @throws ModelValidationException
     * @throws ExitException
     * @throws InvalidConfigException
     * @throws InvalidParamException
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
     * @param int $key
     * @param Column $column
     * @return string
     */
    protected function getColumnLabel($key, $column)
    {
        $label = Yii::t('export', 'Column') . ' ' . ($key + 1);

        if (!empty($column->label)) {
            $label = $column->label;
        }
        elseif (!empty($column->header)) {
            $label = $column->header;
        }
        elseif (!empty($column->attribute)) {
            $label = $this->getAttributeLabel($column->attribute);
        }
        elseif (!$column instanceof DataColumn) {
            $label = Inflector::camel2words((new \ReflectionClass($column))->getShortName());
        }

        return trim(strip_tags(preg_replace('#<br\s*\/?>#i', ' ', $label)));
    }

    /**
     * @param ActiveRecord $model
     * @param string $key
     * @param int $index
     * @throws InvalidParamException
     * @return array
     */
    public function getRow($model, $key, $index)
    {
        $row = [];

        foreach ($this->visibleColumns as $column) {
            if ($column instanceof SerialColumn) {
                $value = $column->renderDataCell($model, $key, $index);
            } elseif ($column instanceof ActionColumn) {
                $value = '';
            } else {
                $format = 'raw';
                if (isset($column->format)) {
                    if (is_array($column->format)) {
                        $column->format = $format;
                    }
                    $format = $column->format;
                }

                if (method_exists($column, 'renderDataCell')) {
                    $value = strip_tags($column->renderDataCell($model, $key, $index));
                } else {
                    if ($column->content === null && method_exists($column, 'getDataCellValue')) {
                        $value = $this->formatter->format($column->getDataCellValue($model, $key, $index), $format);
                    } else {
                        $value = call_user_func($column->content, $model, $key, $index, $column);
                    }
                }

            }

            if (empty($value) && !empty($column->attribute) && $column->attribute !== null) {
                $value = ArrayHelper::getValue($model, $column->attribute, '');
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

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @throws ModelValidationException
     * @throws BadRequestHttpException
     */
    private function _actionInit()
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


        $dateFrom = (new \DateTime($this->filterModel->connect_time_to))->modify('-1 day')->format(DateTimeZoneHelper::DATE_FORMAT);
        $keyData = [$this->filterModel->connect_time_from, $dateFrom];
        foreach ($this->filterModel as $property => $value) {
            if ($value) {
                $keyData[] = substr($property, 0, 1) . (is_array($value) ? implode('_', $value) : $value);
            }
        }

        if (($key = $driver->createHeader('trunk_calls_' . implode('_', $keyData) . '_' . time(), $headerColumns)) === false) {
            throw new BadRequestHttpException('Cannot create export file');
        }

        \Yii::$app->response->content = Json::encode([
            'total' => $this->provider->getTotalCount(),
            'key' => $key,
        ]);
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
            throw new ModelValidationException($input);
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
            'total' => $this->provider->getTotalCount(),
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