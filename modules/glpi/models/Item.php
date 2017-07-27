<?php

namespace app\modules\glpi\models;

use yii\base\Model;

class Item extends Model
{
    public $id;
    public $name;
    public $content;
    public $status;
    public $date_creation;
    public $priority;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'status', 'priority'], 'integer'],
            [['name', 'content', 'date_creation'], 'string'],
        ];
    }

    /**
     * Создать массив моделей из двухуровневого массива данных
     *
     * @param array $items
     * @return self[]
     */
    public static function createFromArray($items)
    {
        $models = [];
        if (!$items) {
            return $models;
        }

        foreach ($items as $item) {
            $model = new self;
            $model->setAttributes($item);
            $models[$model->id] = $model;
        }

        return $models;
    }
}