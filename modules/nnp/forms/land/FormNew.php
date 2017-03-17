<?php

namespace app\modules\nnp\forms\land;

use app\modules\nnp\models\Land;

class FormNew extends Form
{
    /**
     * @return Land
     */
    public function getLandModel()
    {
        return new Land();
    }
}