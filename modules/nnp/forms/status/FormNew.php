<?php

namespace app\modules\nnp\forms\status;

use app\modules\nnp\models\Status;

class FormNew extends Form
{
    /**
     * @return Status
     */
    public function getStatusModel()
    {
        return new Status();
    }
}