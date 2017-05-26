<?php

namespace app\modules\uu\forms;

use app\modules\uu\models\TariffStatus;

class TariffStatusAddForm extends TariffStatusForm
{
    /**
     * @return TariffStatus
     * @throws \InvalidArgumentException
     */
    public function getTariffStatusModel()
    {
        return new TariffStatus;
    }
}