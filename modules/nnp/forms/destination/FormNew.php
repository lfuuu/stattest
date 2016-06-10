<?php

namespace app\modules\nnp\forms\destination;

use app\modules\nnp\models\Destination;

class FormNew extends Form
{
    /**
     * @return Destination
     */
    public function getDestinationModel()
    {
        return new Destination();
    }
}