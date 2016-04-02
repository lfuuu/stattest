<?php

namespace app\classes\voip\forms;

use app\models\NumberType;

class NumberTypeFormNew extends NumberTypeForm
{
    /**
     * @return NumberType
     */
    public function getNumberTypeModel()
    {
        return new NumberType();
    }
}