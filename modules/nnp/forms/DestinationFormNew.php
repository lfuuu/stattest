<?php

namespace app\modules\nnp\forms;

use app\modules\nnp\models\Destination;

class DestinationFormNew extends DestinationForm
{
    /**
     * @return Destination
     */
    public function getDestinationModel()
    {
        return new Destination();
    }
}