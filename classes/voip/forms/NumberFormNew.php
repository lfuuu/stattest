<?php

namespace app\classes\voip\forms;

class NumberFormNew extends NumberForm
{
    /**
     * @return \app\models\Number
     */
    public function getNumberModel()
    {
        return new \app\models\Number();
    }
}