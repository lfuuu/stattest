<?php

namespace app\modules\nnp\forms;

use app\modules\nnp\models\Operator;

class OperatorFormNew extends OperatorForm
{
    /**
     * @return Operator
     */
    public function getOperatorModel()
    {
        return new Operator();
    }
}