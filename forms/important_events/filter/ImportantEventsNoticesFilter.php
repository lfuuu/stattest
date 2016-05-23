<?php

namespace app\forms\important_events\filter;

use Yii;
use yii\data\ArrayDataProvider;
use app\classes\validators\ArrayValidator;

class ImportantEventsNoticesFilter extends \yii\db\ActiveRecord
{

    public $event;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['event',], ArrayValidator::className()],
        ];
    }

    /**
     * @param array $data
     * @return ArrayDataProvider
     */
    public function search($data = [])
    {
        $eventFilter = $this->event;

        if (count($this->event)) {
            $data = array_filter($data, function($row) use ($eventFilter) {
                return in_array($row['event'], $eventFilter, true);
            });
        }

        return new ArrayDataProvider([
            'allModels' => $data,
            'sort' => false,
            'pagination' => false,
        ]);
    }
}