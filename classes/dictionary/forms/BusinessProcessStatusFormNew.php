<?php

namespace app\classes\dictionary\forms;

use app\models\BusinessProcessStatus;

class BusinessProcessStatusFormNew extends BusinessProcessStatusForm
{
    /**
     * @return BusinessProcessStatus
     */
    public function getStatusModel()
    {
        return new BusinessProcessStatus(['color' => '#ffffff']);
    }
}