<?php

namespace app\modules\nnp\forms;

use app\modules\nnp\models\Region;

class RegionFormNew extends RegionForm
{
    /**
     * @return Region
     */
    public function getRegionModel()
    {
        return new Region();
    }
}