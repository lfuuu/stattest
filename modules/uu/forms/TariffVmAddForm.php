<?php

namespace app\modules\uu\forms;

use app\modules\uu\models\TariffVm;

class TariffVmAddForm extends TariffVmForm
{
    /**
     * @return TariffVm
     * @throws \InvalidArgumentException
     */
    public function getTariffVmModel()
    {
        return new TariffVm;
    }
}