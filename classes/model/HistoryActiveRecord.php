<?php

namespace app\classes\model;

use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\db\ActiveRecord;
use app\models\HistoryVersion;
use app\models\User;

class HistoryActiveRecord extends ActiveRecord
{
    private $historyVersionStoredDate = null;
    private $historyVersionRequestedDate = null;

    public function getHistoryVersionStoredDate()
    {
        return $this->historyVersionStoredDate;
    }

    public function setHistoryVersionStoredDate($date)
    {
        $this->historyVersionStoredDate = $date;
    }

    public function getHistoryVersionRequestedDate()
    {
        return $this->historyVersionRequestedDate;
    }

    public function setHistoryVersionRequestedDate($date)
    {
        $this->historyVersionRequestedDate = $date;
    }

    /**
     * @return $this
     */
    public function loadVersionOnDate($date)
    {
        return HistoryVersion::loadVersionOnDate($this, $date);
    }

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

    private function isNeedHistoryVersionSaveModel()
    {
        if ($this->isNewRecord || !$this->getHistoryVersionStoredDate()) {
            return true;
        } else {
            $date = $this->getHistoryVersionStoredDate();
            if (strtotime($date) < time() && HistoryVersion::find()
                    ->andWhere([
                        'model' => HistoryVersion::prepareClassName($this->className()),
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
            $model = new \app\models\HistoryVersion($queryData);
        }

        $model->data_json = json_encode($this->toArray(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
        $model->save();
    }
}