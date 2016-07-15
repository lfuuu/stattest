<?php

namespace app\models\light_models;

use yii\base\Model;

class ImportantEventLight extends Model
{

    public
        $id,
        $client_id,
        $source_id,
        $date,
        $event;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'client_id', 'source_id'], 'integer'],
            [['event', 'date'], 'string'],
        ];
    }

}