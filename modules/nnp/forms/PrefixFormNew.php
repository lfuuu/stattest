<?php

namespace app\modules\nnp\forms;

use app\modules\nnp\models\Prefix;

class PrefixFormNew extends PrefixForm
{
    /**
     * @return Prefix
     */
    public function getPrefixModel()
    {
        return new Prefix();
    }
}