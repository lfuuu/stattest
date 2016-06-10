<?php

namespace app\modules\nnp\forms\operator;

use app\modules\nnp\models\Operator;

class FormNew extends Form
{
    /**
     * @return Operator
     */
    public function getOperatorModel()
    {
        return new Operator();
    }
}