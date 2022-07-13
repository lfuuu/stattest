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
        $event,
        $country_code,
        $login_email;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'client_id', 'source_id', 'country_code',], 'integer'],
            [['event', 'date', 'login_email'], 'string'],
        ];
    }

}