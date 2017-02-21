<?php

namespace app\classes\dictionary\forms;

use app\models\Region;

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