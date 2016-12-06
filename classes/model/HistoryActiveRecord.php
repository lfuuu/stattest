<?php

namespace app\classes\model;

use app\classes\Assert;
use Yii;
use yii\db\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\HistoryVersion;
use app\models\User;

class HistoryActiveRecord extends ActiveRecord
{

    private
        $historyVersionStoredDate = null,
        $historyVersionRequestedDate = null;

    // Свойства модели которые не должны обновляться при загрузки версионной модели
    public
        $attributesProtectedForVersioning = [];

    // Свойства модели, которые должны обновляться при загрузки версионной модели
    public
        $attributesAllowedForVersioning = [];


    /**
     * @return null
     */
    public function getHistoryVersionStoredDate()
    {
        return $this->historyVersionStoredDate;
    }

    /**
     * @param $date
     */
    public function setHistoryVersionStoredDate($date)
    {
        $this->historyVersionStoredDate = $date;
    }

    /**
     * @return null
     */
    public function getHistoryVersionRequestedDate()
    {
        return $this->historyVersionRequestedDate;
    }

    /**
     * @param $date
     */
    public function setHistoryVersionRequestedDate($date)
    {
        $this->historyVersionRequestedDate = $date;
    }

    /**
     * @param bool|true $runValidation
     * @param null|array $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $result =
            $this->isNeedHistoryVersionSaveModel()
                ? parent::save($runValidation, $attributeNames)
                : true;

        if ($result) {
            $this->createHistoryVersion();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getDateList()
    {
        $months = [
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сенября',
            'октября',
            'ноября',
            'декабря'
        ];
        return
            ($this->historyVersionStoredDate ? [$this->historyVersionStoredDate => $this->historyVersionStoredDate] : [])
            +
            [
                date(DateTimeZoneHelper::DATE_FORMAT) => 'Текущую дату',
                date('Y-m-01', strtotime('- 1 month')) => 'С 1го ' . $months[date('m', strtotime('- 1 month')) - 1],
                date('Y-m-01') => 'С 1го ' . $months[date('m') - 1],
                date('Y-m-01', strtotime('+ 1 month')) => 'С 1го ' . $months[date('m', strtotime('+ 1 month')) - 1],
                '' => 'Выбраную дату'
            ];
    }

    /**
     * @return bool
     */
    private function isNeedHistoryVersionSaveModel()
    {
        if ($this->isNewRecord || !$this->getHistoryVersionStoredDate()) {
            return true;
        } else {
            $date = $this->getHistoryVersionStoredDate();
            if (strtotime($date) < time() && HistoryVersion::find()
                    ->andWhere([
                        'model' => $this->prepareClassName($this->className()),
                        'model_id' => $this->id
                    ])
                    ->andWhere(['<=', 'date', date(DateTimeZoneHelper::DATE_FORMAT)])
                    ->andWhere(['>', 'date', $date])
                    ->count() == 0
            ) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @inheritdoc
     */
    private function createHistoryVersion()
    {
        if ($this->isNewRecord || !$this->getHistoryVersionStoredDate()) {
            $date = date(DateTimeZoneHelper::DATE_FORMAT);
        } else {
            $date = $this->getHistoryVersionStoredDate();
        }

        $queryData = [
            'model' => substr(get_class($this), 11),
            'model_id' => $this->primaryKey,
            'date' => $date,
            'data_json' => json_encode($this->toArray(),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
            'user_id' => Yii::$app->user->getId() ?: User::SYSTEM_USER_ID,
        ];

        $model = HistoryVersion::findOne([
            'model' => substr(get_class($this), 11),
            'model_id' => $this->primaryKey,
            'date' => $date,
        ]);

        if (!$model) {
            $model = new HistoryVersion($queryData);
        }

        $model->data_json = json_encode($this->toArray(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
        $model->save();
    }


    /**
     * Загружает в модель данные на заданную дату
     *
     * @param null|string $date
     * @return HistoryActiveRecord
     * @internal param HistoryActiveRecord $model
     */
    public function loadVersionOnDate($date = null)
    {
        if (null === $date) {
            return $this;
        }

        $modelName = $this->prepareClassName($this->className());

        $historyModel = HistoryVersion::find()
            ->andWhere(['model' => $modelName])
            ->andWhere(['model_id' => $this->primaryKey])
            ->andWhere(['<=', 'date', $date])
            ->orderBy('date DESC')->one();

        if ($historyModel) {
            $this->fillHistoryDataInModel(json_decode($historyModel['data_json'], true));
            $this->setHistoryVersionStoredDate($historyModel['date']);
        }
        $this->setHistoryVersionRequestedDate($date);

        return $this;
    }

    /**
     * Подготавливает названия класса, для работы с историей
     *
     * @param string $className
     * @return string
     */
    public function prepareClassName($className)
    {
        if (strpos($className, 'app\\models\\') !== false) {
            $className = substr($className, strlen('app\\models\\'));
        }
        return $className;
    }

    /**
     * Заполняет текущую модель данными из истории
     *
     * @param array $versionData
     * @internal param HistoryActiveRecord $model
     */
    public function fillHistoryDataInModel(array $versionData)
    {
        if ($this instanceof HistoryActiveRecord) {

            //модели со списком атрибутов доступных для версионирования
            if ($this->attributesAllowedForVersioning) {
                $newVersionData = [];

                foreach($this->attributesAllowedForVersioning as $key) {
                    if (isset($versionData[$key])) {
                        $newVersionData[$key] = $versionData[$key];
                    }
                }
                $versionData = $newVersionData;

                //модели с атрибутами, которые надо исключить из версионирования
            } elseif ($this->attributesProtectedForVersioning) {
                $protectedAttributes = array_flip($this->attributesProtectedForVersioning);

                foreach ($this as $key => $value) {
                    if (isset($protectedAttributes[$key])) {
                        unset($versionData[$key]);
                    }
                }
            }
        }

        $this->setAttributes($versionData, false);
    }
}