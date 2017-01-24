<?php

namespace app\classes\model;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\HistoryVersion;
use app\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

/**
 * Class HistoryActiveRecord
 *
 * @property int $id
 */
class HistoryActiveRecord extends ActiveRecord
{

    private
        $_historyVersionStoredDate = null,
        $_historyVersionRequestedDate = null;


    public
        $attributesProtectedForVersioning = [], // Свойства модели которые не должны обновляться при загрузки версионной модели
        $attributesAllowedForVersioning = [];   // Свойства модели, которые должны обновляться при загрузки версионной модели


    /**
     * @return null|string Дата сохранения версии
     */
    public function getHistoryVersionStoredDate()
    {
        return $this->_historyVersionStoredDate;
    }

    /**
     * @param string $date Формат: Y-m-d
     */
    public function setHistoryVersionStoredDate($date)
    {
        $this->_historyVersionStoredDate = $date;
    }

    /**
     * @return null|string Дата запроса версии
     */
    public function getHistoryVersionRequestedDate()
    {
        return $this->_historyVersionRequestedDate;
    }

    /**
     * @param string $date Формат: Y-m-d
     */
    public function setHistoryVersionRequestedDate($date)
    {
        $this->_historyVersionRequestedDate = $date;
    }

    /**
     * @param bool $runValidation
     * @param array $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $result = $this->_isNeedHistoryVersionSaveModel() ?
            parent::save($runValidation, $attributeNames) :
            true;

        if ($result) {
            $this->_createHistoryVersion();
        }

        return $result;
    }

    /**
     * @return string[]
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
            ($this->_historyVersionStoredDate ? [$this->_historyVersionStoredDate => $this->_historyVersionStoredDate] : []) +
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
    private function _isNeedHistoryVersionSaveModel()
    {
        if ($this->isNewRecord || !$this->getHistoryVersionStoredDate()) {
            return true;
        } else {
            $date = $this->getHistoryVersionStoredDate();
            if (strtotime($date) < time() && !HistoryVersion::find()
                    ->andWhere([
                        'model' => $this->getClassName(),
                        'model_id' => $this->id
                    ])
                    ->andWhere(['<=', 'date', date(DateTimeZoneHelper::DATE_FORMAT)])
                    ->andWhere(['>', 'date', $date])
                    ->count()
            ) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @inheritdoc
     * @throws \app\exceptions\ModelValidationException
     */
    private function _createHistoryVersion()
    {
        if ($this->isNewRecord || !$this->getHistoryVersionStoredDate()) {
            $date = date(DateTimeZoneHelper::DATE_FORMAT);
        } else {
            $date = $this->getHistoryVersionStoredDate();
        }

        $queryData = [
            'model' => $this->getClassName(),
            'model_id' => $this->primaryKey,
            'date' => $date,
            'data_json' => json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
            'user_id' => Yii::$app->user->getId() ?: User::SYSTEM_USER_ID,
        ];

        $model = HistoryVersion::findOne([
            'model' => $this->getClassName(),
            'model_id' => $this->primaryKey,
            'date' => $date,
        ]);

        if (!$model) {
            $model = new HistoryVersion($queryData);
        }

        $model->data_json = json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
        if (!$model->save()) {
            throw new ModelValidationException($model);
        }
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

        $modelName = $this->getClassName();

        $historyModel = HistoryVersion::find()
            ->andWhere(['model' => $modelName])
            ->andWhere(['model_id' => $this->primaryKey])
            ->andWhere(['<=', 'date', $date])
            ->orderBy(['date' => SORT_DESC])
            ->one();

        if ($historyModel) {
            $this->fillHistoryDataInModel($this->_historyModelJsonDecode($historyModel));
            $this->setHistoryVersionStoredDate($historyModel['date']);
        } else {
            // если нет истории на вызыванную дату, то берем первое сохранение версии
            $historyModel = HistoryVersion::find()
                ->andWhere(['model' => $modelName])
                ->andWhere(['model_id' => $this->primaryKey])
                ->andWhere(['>', 'date', $date])
                ->orderBy(['date' => SORT_ASC])
                ->one();

            if ($historyModel) {
                $this->fillHistoryDataInModel($this->_historyModelJsonDecode($historyModel));
                $this->setHistoryVersionStoredDate($date);
            }
        }

        $this->setHistoryVersionRequestedDate($date);

        return $this;
    }

    /**
     * Декодирует Json и выкидывает ошибку с данными о неверной модели
     *
     * @param HistoryVersion $historyModel
     * @return mixed
     */
    private function _historyModelJsonDecode(HistoryVersion $historyModel)
    {
        $json = json_decode($historyModel->data_json, true);

        if (!$json) {
            throw new InvalidParamException(sprintf('Error decode json (%s:%s, %s)',
                $historyModel->model,
                $historyModel->model_id,
                $historyModel->date
            ));
        }

        return $json;
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
     * Вернуть класс + ID
     *
     * @param HistoryActiveRecord|HistoryActiveRecord[] $models Одна или массив моделей, которые надо искать
     * @param array $deleteModel Исходная модель (можно свежесозданную и несохраненную), поле и значение, которые надо искать среди удаленных моделей
     * @return string
     */
    public static function getHistoryIds($models, $deleteModel = [])
    {
        if (!is_array($models)) {
            $models = [$models];
        }

        $historyIdPhp = [];
        foreach ($models as $model) {
            if ($model->isNewRecord) {
                continue;
            }

            $historyIdPhp[] = [$model->getClassName(), $model->id];
        }

        if (count($deleteModel) === 3) {
            list($model, $fieldName, $fieldValue) = $deleteModel;
            $historyIdPhp[] = [$model->getClassName(), $fieldName, $fieldValue];
        }

        $historyIdJson = json_encode($historyIdPhp);
        $historyIdJson = str_replace('"', "'", $historyIdJson); // чтобы не конфликтовать с кавычками html-атрибута
        return $historyIdJson;
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

            // модели со списком атрибутов доступных для версионирования
            if ($this->attributesAllowedForVersioning) {
                $newVersionData = [];

                foreach ($this->attributesAllowedForVersioning as $key) {
                    if (isset($versionData[$key])) {
                        $newVersionData[$key] = $versionData[$key];
                    }
                }

                $versionData = $newVersionData;

                // модели с атрибутами, которые надо исключить из версионирования
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