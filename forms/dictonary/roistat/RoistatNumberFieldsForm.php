<?php

namespace app\forms\dictonary\roistat;

use app\models\RoistatNumberFields;

class RoistatNumberFieldsForm extends RoistatNumberFields
{
    public $fields_arr;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                ['fields_arr', 'app\classes\validators\ArrayValidator']
            ]);
    }
}
