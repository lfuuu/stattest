<?php

namespace app\modules\nnp\forms\prefix;

use app\modules\nnp\models\Prefix;

class FormNew extends Form
{
    /**
     * @return Prefix
     */
    public function getPrefixModel()
    {
        return new Prefix();
    }
}