<?php

namespace app\modules\nnp\forms\package;

use app\modules\nnp\models\Package;

class FormNew extends Form
{
    /**
     * @return Package
     */
    public function getPackageModel()
    {
        return new Package();
    }
}