<?php

namespace app\modules\nnp\forms\region;

use app\modules\nnp\models\Region;

class FormNew extends Form
{
    /**
     * @return Region
     */
    public function getRegionModel()
    {
        return new Region();
    }
}