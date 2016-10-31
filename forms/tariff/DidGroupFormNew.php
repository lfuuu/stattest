<?php

namespace app\forms\tariff;

use app\models\DidGroup;

class DidGroupFormNew extends DidGroupForm
{
    /**
     * @return DidGroup
     */
    public function getDidGroupModel()
    {
        return new DidGroup();
    }
}